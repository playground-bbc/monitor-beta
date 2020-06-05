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
     * @return $count the total record
     * @throws NotFoundHttpException if the model cannot be found
     */
    public static function setBoxPropertiesTwitter($alertId,$resourceId,$term){

        $where = ['alertId' => $alertId,'resourcesId' => $resourceId];
        if($term != ""){
            $where['term_searched'] = $term;
        }

        $properties = [
            'retweet_count' => [
                'total' => 0,
                'background_color' => 'black',
                'title' => 'Total Retweets',
                'icon' => 'some'
            ],
            'favorite_count' => [
                'total' => 0,
                'background_color' => 'black',
                'title' => 'Total Favorites',
                'icon' => 'some'
            ],
            'tweets_count' => [
                'total' => 0,
                'background_color' => 'black',
                'title' => 'Total Tweets',
                'icon' => 'some'
            ],

        ];
        $db = \Yii::$app->db;
        $duration = 60; 

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

}

?>