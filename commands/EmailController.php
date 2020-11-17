<?php

namespace app\commands;

use yii\console\Controller;
use yii\console\ExitCode;
use app\models\Dictionaries;
/**
 *
 * This command is provided as email.
 *
 */
class EmailController extends Controller
{
    /**
     * This command echoes what you have entered as the message.
     * @param string $message the message to be echoed.
     * @return int Exit code
     */
    public function actionIndex()
    {   
        $test = \Yii::$app->runAction('monitor/api/insights/content-page?resourceId=5');
        return ExitCode::OK;
    }

    public function actionEmail()
    {
        // set main layout
        //\Yii::$app->mailer->htmlLayout = "@app/mail/layouts/html";
        // collect messages
        \Yii::$app->mailer->compose('insights')
        ->setFrom('monitormtg@gmail.com')
        ->setTo("spiderbbc@gmail.com")->setSubject("Insigths de la Cuenta 📝: Mundo Lg")->send();
        // send messages
        //\Yii::$app->mailer->sendMultiple($messages);
    }
}
