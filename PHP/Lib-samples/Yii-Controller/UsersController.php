<?php

class UsersController extends Controller {

    /**
     * @var string the default layout for the views. Defaults to '//layouts/column2', meaning
     * using two-column layout. See 'protected/views/layouts/column2.php'.
     */
    public $layout = '//layouts/column2';

    /**
     * @return array action filters
     */
    public function filters() {
        return array(
            'accessControl', // perform access control for CRUD operations
            'postOnly + delete', // we only allow deletion via POST request
        );
    }

    /**
     * Specifies the access control rules.
     * This method is used by the 'accessControl' filter.
     * @return array access control rules
     */
    public function accessRules() {
        return array(
            array('allow', 
                'actions' => array('create', 'confirmcredentials', 'forgotpassword', 'changepassword'),
                'users' => array('*'),
            ),
            array('allow', 
                'actions' => array('create', 'update', 'view'),
                'users' => array('@'),
            ),
            array('allow', 
                'actions' => array('admin', 'index', 'delete'),
                'users' => array('admin'),
            ),
            array('deny', // deny all users
                'users' => array('*'),
            ),
        );
    }

    /**
     * Displays a particular model.
     * @param integer $id the ID of the model to be displayed
     */
    public function actionView($id) {
        $model = $this->loadModel($id);
        $params = array('User' => $model);
        if (!Yii::app()->user->checkAccess('updateSelf', $params) && !Yii::app()->user->checkAccess('admin')) {
            throw new CHttpException(403, 'Usted no esta autorizado para acceder esta area');
        }
        $this->render('view', array(
            'model' => $this->loadModel($id),
        ));
    }

    /**
     * Creates a new model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     */
    public function actionCreate() {
        $userModel  = new EUsers;
        $this->performAjaxValidation($userModel);
        if (isset($_POST['EUsers'])) {
            $userModel->attributes = $_POST['EUsers'];
            $userModel->active = 0;
            $userModel->password = crypt($userModel->password, 'meme');
            if ($userModel->save()) {
                $this->redirect(array('merchantlocation/create/', 'id' => $userModel->id));
            }
        }
        $this->layout = '//layouts/column1';
        $this->render('create', array(
            'model' => $userModel,
        ));
    }

    public function actionConfirmcredentials($h = null) {
        $loginModel         = new LoginForm;
        $this->layout = '//layouts/column1';
        if(!empty($h)){
            $hashModel          = Hash::model()->findByPk($h);
            if($hashModel->active) {
                $userModel          = $this->loadModel($hashModel->user_id);
                $hashModel->active  = 0;
                $userModel->active  = 1;
                if($hashModel->save() && $userModel->save()) {
                    Yii::app()->user->setFlash('success', 'Tu cuenta ha sido activada. Favor insertar usuario y contraseña para entrar.');
                    $this->redirect(Yii::app()->homeUrl . 'site/login/');
                } else {
                    Yii::app()->user->setFlash('error', 'Hubo un problema con la activacion de tu cuenta.');
                    $this->redirect(Yii::app()->homeUrl . 'site/login/');
                }
            } else {
                Yii::app()->user->setFlash('notice', 'Hmmm... Algo paso. Vemos que tu cuenta ya esta activada, usa tus credenciales para entrar '.CHtml::link('AQUI', array('site/login')));
            }
        } else {
            $this->render('confirmcredentials', array(
                'h' => $h
            ));
        }
    }

    /**
     * Updates the User model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * We check for the logged in user, if it is not admin, then we restrict some of the functionalities
     * by changing the scenario on the model. 
     * @param integer $id the ID of the model to be updated
     */
    public function actionUpdate($id) {
        $model = $this->loadModel($id);
        $title = $model->title;
        $model->scenario = 'update';
        $params = array('User' => $model);
        if (!Yii::app()->user->checkAccess('updateSelf', $params) && !Yii::app()->user->checkAccess('admin')) {
            throw new CHttpException(403, 'Usted no esta autorizado para acceder esta area');
        }
        if (isset($_POST['EUsers'])) {
            $model->attributes = $_POST['EUsers'];
            
            if(Yii::app()->user->getId() === 1) {
                $model->title = 'Administrator';
            } else {
                $model->title = $title;
            }
            
            $model->password = crypt($model->password, 'meme');
            if ($model->save()) {
                Yii::app()->user->setFlash('success', 'Tu cuenta ha sido activada.');
                $this->redirect(array('view', 'id' => $model->id));
            }
                
        }
        $this->render('update', array(
            'model' => $model,
        ));
    }
    
    public function actionForgotPassword() {
        $mUser = new EUsers;
        if(isset($_POST['ForgotpasswordForm'])) {
            $mUser->attributes = $_POST['ForgotpasswordForm'];
            $userModel = $mUser->model()->findAll('email=:email', array('email' => $mUser->email));
            if (count($userModel) != 1) {
                Yii::app()->user->setFlash('error', 'No pudimos encontrar ese usuario');
            } else {
                $sHash              = createHash();
                $hashModel          = new Hash();
                $userModel          = $userModel[0];
                $hashModel->user_id = $userModel->id;
                $hashModel->hash    = $sHash;
                $hashModel->active  = 1;
                $hashModel->save();
                
                $subject = 'Reset Password | EconomizaPR.com';
                $message = Yii::t('user', 'Hola') . ' ' . $userModel->username . ',
                ' . Yii::t('user', 'Nos informaron que necesitas cambiar tu contraseña. Por favor ve a este link y podras cambiarla.') . '
                ' . Yii::t('user', 'Nota: Este link funciona una sola vez.') . '
                ' . Yii::t('user', 'Click aqui para cambiarla') . ': ' . 'http://economizapr.com/users/changepassword/h/' . $hashModel->hash
                ;

                sendEmail($userModel->email, $subject, $message);
                Yii::app()->user->setFlash('success', 'Enviamos un link a tu email para que puedas cambiar tu contraseña...');
            }            
        }
        $this->layout = '//layouts/column1';
        $this->render('forgotpassword', array('model' => new ForgotpasswordForm()));
    }

    public function actionChangePassword($h) {
        $model = new changepasswordForm();
        if(!empty($h)){
            $hashModel = Hash::model()->findByPk($h);
            if($hashModel->active) {
                $userModel          = $this->loadModel($hashModel->user_id);
                $hashModel->active  = 0;
                if (isset($_POST['changepasswordForm'])) {
                    $model->attributes = $_POST['changepasswordForm'];
                    if ($model->validate() && $model->changePassword($userModel)) {
                        Yii::app()->user->setFlash('success', '<strong>Éxito!</strong> Su contraseña fue cambiada. Puedes entrar al website ahora.');
                        $hashModel->save();
                        $this->redirect('/site/login');
                    }
                }
            } else {
                Yii::app()->user->setFlash('error', 'Este link ya esta caducado, por favor intenta de nuevo.');
                //Yii::app()->end();
            }
        } else {
            Yii::app()->user->setFlash('error', 'Hubo un problema con tu link, por favor intenta de nuevo.');
        }
        $this->layout = '//layouts/column1';
        $this->render('changepassword', array('model' => $model));
    }
    
    /**
     * Deletes a particular model.
     * If deletion is successful, the browser will be redirected to the 'admin' page.
     * @param integer $id the ID of the model to be deleted
     */
    public function actionDelete($id) {
        $this->loadModel($id)->delete();

        // if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
        if (!isset($_GET['ajax']))
            $this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('admin'));
    }

    /**
     * Lists all models.
     */
    public function actionIndex() {
        $dataProvider = new CActiveDataProvider('EUsers');
        $this->render('index', array(
            'dataProvider' => $dataProvider,
        ));
    }

    /**
     * Manages all models.
     */
    public function actionAdmin() {
        $model = new EUsers('search');
        $model->unsetAttributes();  // clear any default values
        if (isset($_GET['EUsers']))
            $model->attributes = $_GET['EUsers'];

        $this->layout = '//layouts/column1';
        $this->render('admin', array(
            'model' => $model,
        ));
    }

    /**
     * Returns the data model based on the primary key given in the GET variable.
     * If the data model is not found, an HTTP exception will be raised.
     * @param integer $id the ID of the model to be loaded
     * @return EUsers the loaded model
     * @throws CHttpException
     */
    public function loadModel($id) {
        $model = EUsers::model()->findByPk($id);
        if ($model === null)
            throw new CHttpException(404, 'Este usuario no pudo ser encontrado.');
        return $model;
    }

    /**
     * Performs the AJAX validation.
     * @param EUsers $model the model to be validated
     */
    protected function performAjaxValidation($model) {
        if (isset($_POST['ajax']) && $_POST['ajax'] === 'eusers-form') {
            echo CActiveForm::validate($model);
            Yii::app()->end();
        }
    }
}
