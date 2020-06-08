<?php
namespace app\helpers;

use yii;
use yii\db\Expression;

/**
 *
 * @author Eduardo Morales <eduardo@montana-studio.com>
 * @group  Montana-Studio LG 
 */

/**
 * DetailHelper wrapper for DetailController function.
 *
 */
class DetailHelper {

    /**
     * return property view box.twitter
     * @param integer $alertId
     * @param integer $resourceId
     * @param string $term
     * @return $properties with properties record
     */
    public static function setBoxPropertiesTwitter($alertId,$resourceId,$term){

        $where = ['alertId' => $alertId,'resourcesId' => $resourceId];
        if($term != ""){
            $where['term_searched'] = $term;
        }

        $properties = [
            'retweet_count' => [
                'id' => random_int(100, 999),
                'total' => 0,
                'background_color' => 'info-box-icon bg-success elevation-1',
                'title' => 'Total Retweets',
                'icon' => 'glyphicon glyphicon-retweet'
            ],
            'favorite_count' => [
                'id' => random_int(100, 999),
                'total' => 0,
                'background_color' => 'info-box-icon bg-danger elevation-1',
                'title' => 'Total Favorites',
                'icon' => 'glyphicon glyphicon-heart'
            ],
            'tweets_count' => [
                'id' => random_int(100, 999),
                'total' => 0,
                'background_color' => 'info-box-icon bg-default elevation-1',
                'title' => 'Total Tweets',
                'icon' => 'glyphicon glyphicon-stats'
            ],

        ];
        $db = \Yii::$app->db;
        $duration = 5; 

        $alertMentions = $db->cache(function ($db) use ($where) {
            return \app\models\AlertsMencions::find()->where($where)->all();
        },$duration); 

        foreach ($alertMentions as $alertMention) {
            if($alertMention->mentionsCount){
                foreach ($alertMention->mentions as $mentions => $mention) {
                    $mention_data = $mention->mention_data;
                    $properties['retweet_count']['total'] += $mention_data['retweet_count'];
                    $properties['favorite_count']['total'] += $mention_data['favorite_count'];
                    $properties['tweets_count']['total']+= 1;
                }

            }
        }

        return $properties; 

    }

    /**
     * return property view box.liveChat
     * @param integer $alertId
     * @param integer $resourceId
     * @param string $term
     * @return $properties with properties record
     */
    public static function setBoxPropertiesLiveChat($alertId,$resourceId,$term){

        $where = ['alertId' => $alertId,'resourcesId' => $resourceId];
        if($term != ""){
            $where['term_searched'] = $term;
        }

        $properties = [
            'tickets_open' => [
                'id' => random_int(100, 999),
                'total' => 0,
                'background_color' => 'info-box-icon bg-success elevation-1',
                'title' => 'Total Tickets Abiertos',
                'icon' => 'glyphicon glyphicon-retweet'
            ],
            'tickets_pending' => [
                'id' => random_int(100, 999),
                'total' => 0,
                'background_color' => 'info-box-icon bg-success elevation-1',
                'title' => 'Total Tickets Pendientes',
                'icon' => 'glyphicon glyphicon-retweet'
            ],
            'tickets_solved' => [
                'id' => random_int(100, 999),
                'total' => 0,
                'background_color' => 'info-box-icon bg-danger elevation-1',
                'title' => 'Total Tickets Solventados',
                'icon' => 'glyphicon glyphicon-heart'
            ],
            'tickets_count' => [
                'id' => random_int(100, 999),
                'total' => 0,
                'background_color' => 'info-box-icon bg-default elevation-1',
                'title' => 'Total Tickets',
                'icon' => 'glyphicon glyphicon-stats'
            ],

        ];
        $db = \Yii::$app->db;
        $duration = 5; 

        $alertMentions = $db->cache(function ($db) use ($where) {
            return \app\models\AlertsMencions::find()->where($where)->all();
        },$duration); 

        $expression = new Expression("`mention_data`->'$.id' AS ticketId");

        foreach ($alertMentions as $alertMention) {
            if($alertMention->mentionsCount){
                $rows = (new \yii\db\Query())
                      ->select($expression)
                      ->from('mentions')
                      ->where(['alert_mentionId' => $alertMention->id])
                      ->groupBy('ticketId')
                      ->all();
            }
        }

        echo "<pre>";
        print_r($rows);
        die();

        return $properties; 

    }

}

?>