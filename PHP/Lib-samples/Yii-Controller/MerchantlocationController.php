<?php

class MerchantlocationController extends Controller {
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
            array('allow', // allow all users to perform 'index' and 'view' actions
                'actions' => array('create'),
                'users' => array('*'),
            ),
            array('allow', // allow authenticated user to perform 'create' and 'update' actions
                'actions' => array('update', 'view'),
                'users' => array('@'),
            ),
            array('allow', // allow admin user to perform 'admin' and 'delete' actions
                'actions' => array('admin', 'delete'),
                'users' => array('admin'),
            ),
            array('deny', // deny all users
                'users' => array('*'),
            ),
        );
    }
    
    public function actionView($id) {
        $model  = $this->loadModel($id);
        $params = array('Location' => $model);
        if (!Yii::app()->user->checkAccess('updateSelfLocation', $params) && !Yii::app()->user->checkAccess('admin')) {
            throw new CHttpException(403, 'Usted no esta autorizado para ver esta area...');
        }
        $this->render('view', array(
            'model' => $this->loadModel($id),
        ));
    }
    
    public function actionCreate($id = '') {
        $userMessage        = null;
        $user_id            = $id;
        $UserLocationModel  = new MerchantLocation;
        $userModel          = EUsers::model()->findByPk($user_id);
        if (isset($_POST['MerchantLocation'])) {
            $UserLocationModel->attributes  = $_POST['MerchantLocation'];
            $UserLocationModel->user_id     = $id;
            if($userModel->title == 'consumidor') {
                $UserLocationModel->firstName       = 'No es aplicable';
                $UserLocationModel->phone           = '0000000000';
                $UserLocationModel->streetAddress   = 'No es aplicable';
                $UserLocationModel->zipcode         = 0;
            }
            if($userModel->title == 'comerciante') {
                $UserLocationModel->age = 35;
            }
            if ($UserLocationModel->save()) {
                $sHash              = createHash();
                $hashModel          = new Hash();
                $hashModel->user_id = $userModel->id;
                $hashModel->hash    = $sHash;
                $hashModel->active  = 1;
                $hashModel->save();
                $userMessage = Yii::t('user', 'Hola') . ' ' . $userModel->username . ',
                ' . Yii::t('user', 'Aqui te enviamos un email para que confirmes tu correo electronico.') . '
                ' . Yii::t('user',  'Para activar tu cuenta, da click aqui') . ' : ' . 
                                    'http://economizapr.com/users/confirmcredentials/h/' . $hashModel->hash;
                sendEmail($userModel->email, 'EconomizaPR.com | Por favor confirma tu email', $userMessage);
                
                $adminMessage = '<!DOCTYPE html>
                                <html>
                                <body>
                                <h2>Un nuevo usuario se registro</h2>
                                <table>
                                    <tr><td>Tipo:</td> <td>'.$userModel->title.'</td></tr>
                                    <tr><td>Email:</td> <td>'.$userModel->email.'</td></tr>
                                    <tr><td>Nombre:</td> <td>'.$UserLocationModel->firstName.'</td></tr>
                                    <tr><td>Edad:</td> <td>'.$UserLocationModel->age.'</td></tr>
                                    <tr><td>Telefono:</td> <td>'.$UserLocationModel->phone.'</td></tr>
                                    <tr><td>Direccion:</td> <td>'.$UserLocationModel->streetAddress.'</td></tr>
                                    <tr><td>Ciudad:</td> <td>'.$UserLocationModel->city.'</td></tr>
                                    <tr><td>Codigo postal:</td> <td>'.$UserLocationModel->zipcode.'</td></tr>
                                </table>
                                </body>
                                </html>';
                sendEmail(Yii::app()->params['adminEmail'], 'EconomizaPR.com (Notificacion de admininistracion)| Nuevo usuario registrado', $adminMessage);
                
                $this->redirect(array('users/confirmcredentials', 'id' => $userModel->id));
            }
        }
        $this->render('create', array(
            'model' => $UserLocationModel,
            'userModel' => $userModel,
        ));
    }
    
    /**
     * Updates a particular model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id the ID of the model to be updated
     */
    public function actionUpdate($id) {
        $MerchantLocationModel  = $this->loadModel($id);
        
        $params = array('Location' => $MerchantLocationModel);
        if (!Yii::app()->user->checkAccess('updateSelfLocation', $params) && !Yii::app()->user->checkAccess('admin')) {
            throw new CHttpException(403, 'Usted no esta autorizado para ver esta area...');
        }
        $userModel              = EUsers::model()->findByPk($id);
        if (isset($_POST['MerchantLocation'])) {
            $MerchantLocationModel->attributes  = $_POST['MerchantLocation'];
            if($userModel->title == 'consumidor') {
                $MerchantLocationModel->firstName       = 'No es aplicable';
                $MerchantLocationModel->phone           = 'No es aplicable';
                $MerchantLocationModel->streetAddress   = 'No es aplicable';
                $MerchantLocationModel->zipcode         = '0';
            }
            if ($MerchantLocationModel->save())
                $this->redirect(array('view', 'id' => $MerchantLocationModel->user_id));
        }
        $this->render('update', array(
            'model' => $MerchantLocationModel,
            'userModel' => $userModel,
        ));
    }
    
    /**
     * Returns the data model based on the primary key given in the GET variable.
     * If the data model is not found, an HTTP exception will be raised.
     * @param integer $id the ID of the model to be loaded
     * @return Coupon the loaded model
     * @throws CHttpException
     */
    public function loadModel($id) {
        $model = MerchantLocation::model()->findByPk($id);
        if ($model === null)
            throw new CHttpException(404, 'Este usuario no ha activado su direccion.');
        return $model;
    }
}
