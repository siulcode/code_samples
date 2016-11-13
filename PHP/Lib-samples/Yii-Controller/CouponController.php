<?php

class CouponController extends Controller {

    public $layout  = '//layouts/column2';
    public $coupons = 'selected_coupon';

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
                'actions' => array('index', 'view', 'select', 'deselect', 'category','coupon', 'getmap', 'deactivate'),
                'users' => array('*'),
            ),
            array('allow', // allow authenticated user to perform 'create' and 'update' actions
                'actions' => array('create', 'delete', 'update', 'getcouponbymerchant', 'getcoupon'),
                'users' => array('@'),
            ),
            array('allow', // allow admin user to perform 'admin' and 'delete' actions
                'actions' => array('admin'),
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
        $params = array('Coupon' => $model);
        if (!Yii::app()->user->checkAccess('updateSelfCoupon', $params) && !Yii::app()->user->checkAccess('admin')) {
            throw new CHttpException(403, 'Usted no esta autorizado para acceder este cupon');
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
        $model = new Coupon;
        $model->author_id = Yii::app()->user->getId();
        if (isset($_POST['Coupon'])) {
            $model->attributes  = $_POST['Coupon'];
            $model->active      = 1; //activate coupon
            $model->cat_id      = (empty($_POST['Coupon']['cat_id'])) ? 24 : $_POST['Coupon']['cat_id']; //force coupon to the others cat if empty
            $model->create_date = new CDbExpression('NOW()');
            $File               = CUploadedFile::getInstance($model,'image');
            if(!is_null($File)){
                $rnd            = rand(0,9999);
                $Filename       = "{$rnd}-{$File}";
                $File->saveAs('/home/users/web/b2705/moo.nmontanez/images/uploads/logo/' . $Filename);
                $model->image   = $Filename;
            } else {
                $model->image = 'badge.png';
            }
            if ($model->save()) {
                $this->redirect(array('view', 'id' => $model->id));                
            }
        }
        $this->layout = '//layouts/column1';
        $this->render('create', array(
            'model' => $model,
        ));
    }

    /**
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id the ID of the model to be updated
     */
    public function actionUpdate($id) {
        $model  = $this->loadModel($id);
        $params = array('Coupon' => $model);
        if (!Yii::app()->user->checkAccess('updateSelfCoupon', $params) && !Yii::app()->user->checkAccess('admin')) {
            throw new CHttpException(403, 'Usted no esta autorizado para acceder este cupon');
        }
        $active = $model->active ? $model->active : 0;
        $imgChange = false;
        if (isset($_POST['Coupon'])) {
            $uploadedFile = CUploadedFile::getInstance($model,'image');
            if(!empty($uploadedFile)) {
                $rnd            = rand(0,9999);
                $Filename       = "{$rnd}-{$uploadedFile}";
                $imgChange      = true;
            } else {
                $_POST['Coupon']['image'] = (!empty($model->image) && $model->image != 'badge.png') ? $model->image : 'badge.png';
            }
            $model->attributes  = $_POST['Coupon'];
            $model->active      = $active;
            if($imgChange) {
                $model->image   = $Filename; 
            }
            if ($model->save()) {
                if(!empty($uploadedFile)) {
                    $uploadedFile->saveAs('/home/users/web/b2705/moo.nmontanez/images/uploads/logo/'.$Filename);
                }
                $this->redirect(array('view', 'id' => $model->id));
            }
        }
        $this->render('update', array(
            'model' => $model,
        ));
    }

    /**
     * Deletes a particular model.
     * If deletion is successful, the browser will be redirected to the 'admin' page.
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
    public function actionIndex($search = '') {
        $criteria = new CDbCriteria();
        $criteria->addSearchCondition('name', $search, true, 'OR');
        $criteria->addBetweenCondition('active', '1', true);
        $dataProvider = new CActiveDataProvider('Coupon', array(
            'criteria'      => $criteria,
            'sort'          => array('defaultOrder' =>'id DESC'),
            'pagination'    => array(
                'pageSize'      => 30,
            )
        ));
        $this->render('index', array(
            'dataProvider' => $dataProvider,
        ));
    }
    
    /**
     * Lists all models by category.
     */
    public function actionCategory($id) {
        $model = new Coupon;
        $pk = $id;

        $this->render('category', array(
            'model'  => $model,
            'pk'     => $pk,
        ));
    }
    
    /**
     * Lists all models by merchant.
     */
    public function actionGetCouponByMerchant() {
        $model = new Coupon;
        $author_id = Yii::app()->user->getId();

        $this->layout = '//layouts/column1';
        $this->render('couponbymerchant', array(
            'model'     => $model,
            'author_id' => $author_id,
        ));
    }
    
    public function actionSelect() {
        if (isset($_POST['id'])) {
            $arSelected = explode('i', $_POST['id']);
            array_pop($arSelected);
            Yii::app()->session[$this->coupons] = $arSelected;
            $this->renderPartial('_print');
        }
    }
    
    /**
     * Select action alias for ajax request
     */
    public function actionCoupon() {
        if (isset($_POST['id'])) {
            $arSelected = explode('i', $_POST['id']);
            array_pop($arSelected);
            Yii::app()->session[$this->coupons] = $arSelected;
            $this->renderPartial('_print');
        } elseif (isset($_POST['remove_id'])) {
            $arSelected = Yii::app()->session[$this->coupons];
            $arRemove = $_POST;
            $arFinal = array_diff($arSelected, $arRemove);
            Yii::app()->session[$this->coupons] = $arFinal;
            
            $this->renderPartial('_print');
        }
    }
    
    public function actionDeselect() {
        if (isset($_POST['remove_id'])) {
            $arSelected = Yii::app()->session[$this->coupons];
            $arRemove = $_POST;
            $arFinal = array_diff($arSelected, $arRemove);
            Yii::app()->session[$this->coupons] = $arFinal;
            
            $this->renderPartial('_print');
        }
    }

    public function actionPrint() {
        if(!empty(Yii::app()->session[$this->coupons])) {
            $this->render('_print');
        }
    }
    
    public function actionGetcoupon() {
        $model      = Coupon::model()->findAllByPk(Yii::app()->session[$this->coupons]);
        $this->updatePrintCount($model);
        $html2pdf   = Yii::app()->ePdf->HTML2PDF();
        $html2pdf->WriteHTML($this->renderPartial('getcoupon', array('model' => $model), true));
        $html2pdf->Output();

        unset(Yii::app()->session[$this->coupons]); //nuke the stored coupons.
    }
    
    /**
     * Gets the map associated with the coupon. 
     * @param type $id
     */
    public function actionGetmap($id) {
        $MerchantLocationModel = MerchantLocation::model()->findByPk($id);
        $this->renderPartial('_map', array(
            'data' => $MerchantLocationModel
        ));
    }
    
    /**
     * Manages all models.
     */
    public function actionAdmin() {
        $model = new Coupon('search');
        
        $model->author_id = Yii::app()->user->getId();
        
        $model->dbCriteria->order = 'create_date desc';
        $model->unsetAttributes();  // clear any default values
        if (isset($_GET['Coupon']))
            $model->attributes = $_GET['Coupon'];

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
        $iDAYS = $settingModel[0]->coupon_days;
        $criteria = new CDbCriteria;
        $criteria->addCondition("create_date < DATE_SUB(NOW(), INTERVAL $iDAYS DAY)");
        $model = new CActiveDataProvider('Coupon', array(
            'criteria' => $criteria,
        ));
        $this->layout = '//layouts/column1';
        $this->render('deactivate', array(
            'model' => $model,
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
        $model = Coupon::model()->findByPk($id);
        if ($model === null)
            throw new CHttpException(404, 'Esta pagina no existe.');
        return $model;
    }

    protected function updatePrintCount($oModel) {
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
     * Performs the AJAX validation.
     * @param Coupon $model the model to be validated
     */
    protected function performAjaxValidation($model) {
        if (isset($_POST['ajax']) && $_POST['ajax'] === 'coupon-form') {
            echo CActiveForm::validate($model);
            Yii::app()->end();
        }
    }

}