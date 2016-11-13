<?php

class BannerController extends Controller {

    public $layout = '//layouts/column2';

    public function filters() {
        return array(
            'accessControl', // perform access control for CRUD operations
            'postOnly + delete', // we only allow deletion via POST request
        );
    }

    public function accessRules() {
        return array(
            array('allow',
                'actions' => array('deactivate'),
                'users' => array('*'),
            ),
            array('allow',
                'actions' => array('cancel', 'success', 'view', 'update', 'create', 'checkout', 'bannerbyowner'),
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
        $this->render('view', array(
            'model' => $this->loadModel($id),
        ));
    }
    
    public function actionCancel() {
        $this->render('cancel');
    }
    
    public function actionSuccess() {
        $this->render('success');
    }

    /**
     * Creates a new model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     */
    public function actionCreate() {
        $bannerModel      = new Banners;
        $bannerTypeModel  = BannerType::model();
        $bannerType       = $bannerTypeModel->findAll();
        $userModel        = EUsers::model()->findByPk(Yii::app()->user->getId());
        
        $this->performAjaxValidation($bannerModel);
        if (isset($_POST['Banners'])) {
            $bannerModel->attributes  = $_POST['Banners'];
            $bannerModel->author_id   = Yii::app()->user->getId();
            $bannerModel->status_pay  = 0;
            if(Yii::app()->user->getId() == 1) {
                $bannerModel->active = 1; //activate if admin is posting this banner
            } else {
                $bannerModel->active = 0; //deactivate banner until paypay is verified
            }
            $bannerModel->create_date = new CDbExpression('NOW()');
            $File                     = CUploadedFile::getInstance($bannerModel,'image');
            if(!is_null($File)){
                $rnd            = rand(0,9999);
                $Filename       = "{$rnd}-{$File}";
                $File->saveAs('/home/users/web/b2705/moo.nmontanez/images/uploads/banner/' . $Filename);
                $bannerModel->image   = $Filename;
            }
            if ($bannerModel->save()) {
                $message = Yii::t('user', 'Hola') . ' ' . 'Un nuevo banner fue creado.' . ',
                ' . Yii::t('user', 'El ID de este banner es: <strong>'.$bannerModel->id.'</strong> y el ID del usuario es: <strong>'.$bannerModel->author_id.'</strong>');
                sendEmail(Yii::app()->params['adminEmail'], 'EconomizaPR.com (Notificacion de admininistracion)| Nuevo banner creado', $message);
                if(Yii::app()->user->checkAccess('admin')) {
                    $this->redirect(array('view', 'id' => $bannerModel->id));
                } else {
                    $criteria = new CDbCriteria();
                    $criteria->condition = "type = '$bannerModel->banner_type'";
                    $adType = BannerType::model()->findAll($criteria);
                    $this->redirect(array('site/payment', 'price'       => $adType[0]->price, 
                                                          'title'       => $bannerModel->title, 
                                                          'bannertype'  => $bannerModel->banner_type, 
                                                          'bannerid'    => $bannerModel->id));                    
                }
            }
        }
        $this->render('create', array(
            'model'      => $bannerModel,
            'bannerType' => $bannerType,
        ));
    }
    
    public function actionCheckout($banner_id = '', $uid = '') {
        $this->render('checkout');
    }
    
    /**
     * @param integer $id the ID of the model to be updated
     */
    public function actionUpdate($id) {
        $model  = $this->loadModel($id);
        $model->scenario = 'update';
        $params = array('Banner' => $model);
        if (!Yii::app()->user->checkAccess('updateSelfBanner', $params) && !Yii::app()->user->checkAccess('admin')) {
            throw new CHttpException(403, 'Usted no esta autorizado para acceder este banner');
        }
        $active = $model->active ? $model->active : 0;
        $imgChange = false;
        if (isset($_POST['Banners'])) {
            $uploadedFile = CUploadedFile::getInstance($model,'image');
            if(!empty($uploadedFile)) {
                $rnd            = rand(0,9999);
                $Filename       = "{$rnd}-{$uploadedFile}";
                $imgChange      = true;
            } else {
                $_POST['Banners']['image'] = (!empty($model->image) && $model->image != 'badge.png') ? $model->image : 'badge.png';
            }
            $model->attributes  = $_POST['Coupon'];
            $model->active      = $active;
            if($imgChange) {
                $model->image   = $Filename;
            }
            if ($model->save()) {
                if(!empty($uploadedFile)) {
                    $uploadedFile->saveAs('/home/users/web/b2705/moo.nmontanez/images/uploads/banner/' . $Filename);
                }
                $this->redirect(array('view', 'id' => $model->id));
            }
        }
        $this->render('update', array(
            'model' => $model,
        ));
    }

    /**
     * @param integer $id the ID of the model to be deleted
     */
    public function actionDelete($id) {
        $this->loadModel($id)->delete();
        if (!isset($_GET['ajax']))
            $this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('admin'));
    }

    /**
     * Lists all models.
     */
    public function actionIndex() {
        $dataProvider = new CActiveDataProvider('Banners');
        $this->render('index', array(
            'dataProvider' => $dataProvider,
        ));
    }

    /**
     * Manages all models.
     */
    public function actionAdmin() {
        $model = new Banners('search');
        $model->unsetAttributes();  // clear any default values
        if (isset($_GET['Banners']))
            $model->attributes = $_GET['Banners'];
        $this->layout = '//layouts/column1';
        $this->render('admin', array(
            'model' => $model,
        ));
    }
    
    /**
     * Manages all models.
     */
    public function actionDeactivate() {
        $setting = new ExpireSetting;
        $settingModel = $setting->findAll();
        $iDAYS = $settingModel[0]->banner_days;
        $criteria = new CDbCriteria;
        $criteria->addCondition("create_date < DATE_SUB(NOW(), INTERVAL $iDAYS DAY)");
        $model = new CActiveDataProvider('Banners', array(
            'criteria' => $criteria,
        ));
        $this->layout = '//layouts/column1';
        $this->render('deactivate', array(
            'model' => $model,
        ));
    }
    
    /**
     * Manages all models.
     */
    public function actionBannerbyowner() {
        $model = new Banners('search');
        $author_id = Yii::app()->user->getId();
        
        $this->layout = '//layouts/column1';
        $this->render('bannerByOwner', array(
            'model' => $model,
            'author_id' => $author_id,
        ));
    }
    
    protected function updateClickCount($oModel) {
        foreach ($oModel as $coupon) {
            if($coupon->count_print == null) {
                $coupon->count_print = 1;
            } else {
                $coupon->count_print++;
            }
            $coupon->update();
        }
    }

    /**
     * @param integer $id the ID of the model to be loaded
     * @return Banners the loaded model
     * @throws CHttpException
     */
    public function loadModel($id) {
        $model = Banners::model()->findByPk($id);
        if ($model === null)
            throw new CHttpException(404, 'The requested page does not exist.');
        return $model;
    }

    /**
     * Performs the AJAX validation.
     * @param Banners $model the model to be validated
     */
    protected function performAjaxValidation($model) {
        if (isset($_POST['ajax']) && $_POST['ajax'] === 'banners-form') {
            echo CActiveForm::validate($model);
            Yii::app()->end();
        }
    }

}
