<?php
namespace app\helpers;

use yii;

/**
 *
 * @author Eduardo Morales <eduardo@montana-studio.com>
 * @group  Montana-Studio LG 
 */

/**
 * EmailHelper wrapper for email functions.
 *
 */
class EmailHelper
{

    /**
	 * [sendCommonWords send emails if is not on cache]
	 * @param  array  $alerts [alerts valid on search]
	 */
    public static function sendCommonWords($alerts){
        // map alert
        $alerts= \yii\helpers\ArrayHelper::map($alerts,'id','config.configSources');
        // Crear una dependencia sobre el tiempo de modificación del archivo common_words_email.txt.
        $dependency = new \yii\caching\FileDependency(['fileName' => 'common_words_email.txt']);
        //cache config
        $cache = \Yii::$app->cache;
        $key = "email_common_words";
        //$cache->delete($key);
        $time_expired = 86400; // seconds in a days
        // get cache
        $data_cache = $cache->get($key);
        

        $alertIdTosendEmail = [];
        $alertsIDs = array_keys($alerts);
       
        // check cache
        if(!$data_cache){
            $data_cache = $alertsIDs;
            $alertIdTosendEmail = $alertsIDs;
        }else{
            foreach($alertsIDs as $id){
                if(!in_array($id,$data_cache)){
                    $data_cache[] = $id;
                    $alertIdTosendEmail[] = $id;
                }
            }
        }
        
        // send  email
        if(count($alertIdTosendEmail)){
            // get all alerts active
            $alerts = \app\models\Alerts::find()->where(['id' => $alertIdTosendEmail,'status' => 1])->All();
            // set array models
            $data = [];
            // loop over
            $index = 0;

            foreach($alerts as $alert){
                $source_data = [];
                foreach($alert->config->sources as $source){
                    $alertMentionsIds = \Yii::$app->db->createCommand(
                        'SELECT alerts_mencions.id FROM alerts_mencions WHERE 
                        alertId = :alertId AND 
                        resourcesId= :resourcesId AND 
                        EXISTS ( SELECT * FROM mentions WHERE mentions.alert_mentionId = alerts_mencions.id )'
                    )->bindValues([
                        ':alertId' => $alert->id,
                        ':resourcesId' => $source->id,
                    ])->queryAll();
                    $ids = \yii\helpers\ArrayHelper::getColumn($alertMentionsIds, 'id');    
                    $where_alertMentions['alert_mentionId'] = $ids;
                    $words = (new \yii\db\Query())
                        ->select(['name','total' => 'SUM(weight)'])
                        ->from('alerts_mencions_words')
                        ->where($where_alertMentions)
                        ->groupBy('name')
                        ->orderBy(['total' => SORT_DESC])
                        ->limit(5)
                        ->all();
                    if(count($words)){
                        $source_data[$source->name] =[
                            'sourceId' => $source->id,
                            'words' => $words
                        ];
                    }
                }// end loop source
                if(count($source_data)){
                    $data[$index]['alert'] = $alert;
                    $data[$index]['sources'] = $source_data;
                    $index++;
                }
            }// end foreach loop alert
            $messages = [];
            for($d = 0 ; $d < sizeOf($data) ; $d++){
                // set main layout
                \Yii::$app->mailer->htmlLayout = "@app/mail/layouts/html";
                // collect messages
                $messages[] = \Yii::$app->mailer->compose('common',['data' => $data[$d]])
                ->setFrom('monitormtg@gmail.com')
                ->setTo($data[$d]['alert']->user->email)->setSubject("Palabras mas Frecuentes 📝: {$data[$d]['alert']->name}");
            }
            if(count($messages)){
                $isSend = \Yii::$app->mailer->sendMultiple($messages);
                $cache->set($key, $data_cache, $time_expired, $dependency);
            }
        }
    }

}