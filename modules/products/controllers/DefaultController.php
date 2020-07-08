<?php

namespace app\modules\products\controllers;

use yii\web\Controller;

/**
 * Default controller for the `products` module
 */
class DefaultController extends Controller
{
    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionCreate()
    {
        return $this->render('create');
    }
}
