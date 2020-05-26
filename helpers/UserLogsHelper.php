<?php
namespace app\helpers;

use yii;

/**
 *
 * @author Eduardo Morales <eduardo@montana-studio.com>
 * @group  Montana-Studio LG 
 */

/**
 * UserLogsHelper wrapper for table user_log db function.
 *
 */
class UserLogsHelper{

    public static function save($userId,$message){
        $model = new \app\modules\user\models\UserLogs();
        $model->userId = $userId;
        $model->message = $message;
        // get ip
        $user_agent = [];
        if(Yii::$app->request->getUserip()){
            $user_agent['ip'] = Yii::$app->request->getUserip();
        }
        
        //get user explore
        $browser = new \Browser();
        if($browser->getBrowser()){
            $user_agent['browser'] = $browser->getBrowser();
            $user_agent['browser_version'] = $browser->getVersion();
        }
        // not empty user_agent
        if(!empty($user_agent)){
            $model->user_agent = $user_agent;
        }
        
        if($model->validate()){
            $model->save();
        }else{
            var_dump($model->errors);
            die();
        }
    }

}


?>