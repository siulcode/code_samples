<?php

class ExpireSettingController extends Controller {

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
                'actions' => array(''),
                'users' => array('*'),
            ),
            array('allow',
                'actions' => array(''),
                'users' => array('@'),
            ),
            array('allow',
                'actions' => array('admin', 'update'),
                'users' => array('admin'),
            ),
            array('deny', // deny all users
                'users' => array('*'),
            ),
        );
    }

    public function actionUpdate($id) {
        $model = $this->loadModel($id);
        if (isset($_POST['ExpireSetting'])) {
            $model->attributes = $_POST['ExpireSetting'];
            if ($model->save())
                $this->redirect(array('admin'));
        }
        $this->render('update', array(
            'model' => $model,
        ));
    }

    /**
     * Manages all models.
     */
    public function actionAdmin() {
        $model = new ExpireSetting('search');
        $model->unsetAttributes();  // clear any default values
        if (isset($_GET['ExpireSetting']))
            $model->attributes = $_GET['ExpireSetting'];

        $this->render('admin', array(
            'model' => $model,
        ));
    }

    public function loadModel($id) {
        $model = ExpireSetting::model()->findByPk($id);
        if ($model === null)
            throw new CHttpException(404, 'The requested page does not exist.');
        return $model;
    }
}
