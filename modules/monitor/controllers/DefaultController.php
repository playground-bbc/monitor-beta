<?php

namespace app\modules\monitor\controllers;

use yii\web\Controller;

/**
 * Default controller for the `monitor` module
 */
class DefaultController extends Controller
{
    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionIndex()
    {
        \Yii::$app->mailer->compose()
        ->setFrom('monitor@lg.montana-studio.com')
        ->setTo('spiderbbc@gmail.com')
        ->setSubject('Email sent from Yii2-Swiftmailer')
        ->send();
        die();
        //return $this->render('index');
    }
}
