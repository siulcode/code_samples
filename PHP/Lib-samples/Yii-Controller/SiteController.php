<?php

class SiteController extends Controller {
    
    /**
     * Declares class-based actions.
     */
    public function actions() {
        return array(
            // captcha action renders the CAPTCHA image displayed on the contact page
            'captcha' => array(
                'class' => 'CCaptchaAction',
                'backColor' => 0xFFFFFF,
            ),
            // page action renders "static" pages stored under 'protected/views/site/pages'
            // They can be accessed via: index.php?r=site/page&view=FileName
            'page' => array(
                'class' => 'CViewAction',
            ),
        );
    }
    
    public function filters() {
        return array(
            'accessControl', // perform access control for CRUD operations
        );
    }

    public function accessRules() {
        return array(
            array('allow',
                'actions' => array('cancel', 'confirm', 'contact', 'captcha', 'error', 'login', 'logout', 'notify', 'payment', 'politicasdeprivacidad', 'quienessomos', 'terminodeuso'),
                'users' => array('*'),
            ),
            array('allow',
                'actions' => array('accountsettings'),
                'users' => array('@'),
            ),
            array('allow', 
                'actions' => array(''),
                'users' => array('admin'),
            ),
            array('deny', // deny all users
                'users' => array('*'),
            ),
        );
    }
    
    /**
     * This is the action to handle external exceptions.
     */
    public function actionError() {
        if ($error = Yii::app()->errorHandler->error) {
            if (Yii::app()->request->isAjaxRequest)
                echo $error['message'];
            else
                $this->render('error', $error);
        }
    }
    
    /**
     * Payment goodies
     */
    public function actionPayment($price, $title, $bannertype, $bannerid) {
        $paypalManager = Yii::app()->getModule('SimplePaypal')->paypalManager;

        $paypalManager->addField('item_name', 'Banner Payment Transaction: '.$bannertype);
        $paypalManager->addField('amount', $price);
        $paypalManager->addField('item_name_1', $title);
        $paypalManager->addField('quantity_1', '2');
        $paypalManager->addField('amount_1', '3');
        $paypalManager->addField('custom', $bannerid);

        //$paypalManager->dumpFields();   // for printing paypal form fields
        $paypalManager->submitPaypalPost();
    }

    /**
     * Action that paypal will respond to. 
     * Example of a PayPal response: 
     * http://economizapr.com/site/confirm&q=success?tx=4G4518232J383804C&st=Completed&amt=0%2e01&cc=USD&cm=41&item_number=
     * @throws CHttpException If not found. 
     */
    public function actionConfirm() {
        if (isset($_GET['q']) && $_GET['q'] == 'success' && (isset($_GET["tx"]) && isset($_GET["st"]) && $_GET["st"] == "Completed")) {
            $bannerModel = Banners::model()->findByPk($_GET['cm']);
            if(!is_null($bannerModel)) {
                $bannerModel->scenario = 'update';
                $bannerModel->active = 1;
                $bannerModel->status_pay = 1;
                if($bannerModel->update(array('active'))) {
                    Yii::app()->user->setFlash('success', 'Tu banner ha sido activado.');
                    $this->redirect('/banner/success/');
                }
            } else {
                throw new CHttpException(404, 'Hubo un problema activando tu banner, por favor contactanos para investigar');
            }
        } else {
            throw new CHttpException(404, 'La pagina que buscas no la pudimos encontrar...');
        }
    }
    
    public function actionNotify() {
        $logCat = 'paypal';
        $paypalManager = Yii::app()->getModule('SimplePaypal')->paypalManager;
        try {
            if ($paypalManager->notify() && $_POST['payment_status'] === 'Completed') {
                $model                  = new PaymentTransaction;
                $model->user_id         = Yii::app()->user->getId();
                $model->mc_gross        = $_POST['mc_gross'];
                $model->payment_status  = $_POST['payment_status'];
                $model->payer_email     = $_POST['payer_email'];
                $model->verify_sign     = $_POST['verify_sign'];
                $model->txn_id          = $_POST['txn_id'];
                $model->payment_type    = $_POST['payment_type'];
                $model->receiver_email  = $_POST['receiver_email'];
                $model->txn_type        = $_POST['txn_type'];
                $model->item_name       = $_POST['item_name'];
                $model->ipn_track_id    = $_POST['ipn_track_id'];
                $model->save();
                /* update user payement status field here */
                Yii::log('ipn: ' . print_r($_POST, 1), CLogger::LEVEL_ERROR, $logCat);
            } else {
                Yii::log('invalid ipn', CLogger::LEVEL_ERROR, $logCat);
            }
        } catch (Exception $e) {
            Yii::log($e->getMessage(), CLogger::LEVEL_ERROR, $logCat);
        }
    }
    
    public function actionCancel($q) {
        if($q === 'cancel') {
            Yii::app()->user->setFlash('notice', 'La transacción fue cancelada.');            
        }
        $this->redirect('/banner/cancel/');
    }

    /**
     * Static pages
     */
    public function actionQuienessomos() {
        $this->layout = '//layouts/column2';
        $this->render('pages/quienessomos');
    }
    
    public function actionTerminodeuso() {
        $this->layout = '//layouts/column2';
        $this->render('pages/terminodeuso');
    }
    
    public function actionPoliticasdeprivacidad() {
        $this->layout = '//layouts/column2';
        $this->render('pages/politicasdeprivacidad');
    }
    
    /**
     * Displays the contact page
     */
    public function actionContact() {
        $model = new ContactForm;
        if (isset($_POST['ContactForm'])) {
            $model->attributes = $_POST['ContactForm'];
            if ($model->validate()) {
                $name = '=?UTF-8?B?' . base64_encode($model->name) . '?=';
                $subject = '=?UTF-8?B?' . base64_encode($model->subject) . '?=';
                $headers = "From: $name <{$model->email}>\r\n" .
                        "Reply-To: {$model->email}\r\n" .
                        "MIME-Version: 1.0\r\n" .
                        "Content-Type: text/plain; charset=UTF-8";

                mail(Yii::app()->params['adminEmail'], $subject, $model->body, $headers);
                Yii::app()->user->setFlash('contact', 'Gracias por contactarnos, le responderemos lo mas pronto sea posible.');
                $this->refresh();
            }
        }
        $this->render('contact', array('model' => $model));
    }

    /**
     * Renders the landing page for editing the user.
     */
    public function actionAccountsettings() {
        $id                     = Yii::app()->user->getId();
        $userModel              = EUsers::model()->findByPk($id);
        $MerchantLocationModel  = MerchantLocation::model()->findByPk($id);
        $this->render('accountsettings', array(
            'MerchantLocationModel' => $MerchantLocationModel,
            'userModel' => $userModel,

        ));            
    }
    
    /**
     * Displays the login page
     */
    public function actionLogin() {
        $model = new LoginForm;
        
        if (isset($_POST['ajax']) && $_POST['ajax'] === 'login-form') {
            echo CActiveForm::validate($model);
            Yii::app()->end();
        }
        if (isset($_POST['LoginForm'])) {
            $model->attributes = $_POST['LoginForm'];
            $record            = EUsers::model()->findByAttributes(array('username' => $model->username));
            if ($record === null) {
                Yii::app()->user->setFlash('error', 'Este usuario no esta en el sistema');
                $this->redirect('login');
            } else {
                if($record->active) {
                    if ($model->validate() && $model->login())
                        $this->redirect(Yii::app()->user->returnUrl);
                } else {
                    Yii::app()->user->setFlash('error', 'Tu cuenta aun no ha sido activada. Enviamos un email a '.$record->email.' para activar tu cuenta.');
                    $this->redirect('login');
                }
            }
        }
        $this->render('login', array('model' => $model));
    }

    /**
     * Logs out the current user and redirect to homepage.
     */
    public function actionLogout() {
        Yii::app()->user->logout();
        $this->redirect(Yii::app()->homeUrl);
    }
}
