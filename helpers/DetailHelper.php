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

        $properties = self::getPropertyBoxByResourceName('Twitter');
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

        $properties = self::getPropertyBoxByResourceName('Live Chat');
        $db = \Yii::$app->db;
        $duration = 5; 

        $alertMentionsIds = $db->cache(function ($db) use ($where) {
            $ids =\app\models\AlertsMencions::find()->select(['id','alertId'])->where($where)->asArray()->all();
            return array_keys(\yii\helpers\ArrayHelper::map($ids,'id','alertId'));
        },$duration); 

        $expression = new Expression("`mention_data`->'$.id' AS ticketId");
        // count number tickets
        // SELECT `mention_data`->'$.id' AS ticketId FROM `mentions` where alert_mentionId = 9 GROUP BY `ticketId` DESC
        $ticketCount = (new \yii\db\Query())
            ->cache($duration)
            ->select($expression)
            ->from('mentions')
            ->where(['alert_mentionId' => $alertMentionsIds])
            ->groupBy(['ticketId'])
            ->count();

        $properties['tickets_count']['total'] = $ticketCount;    
        // count number tickets open
        $status = ['tickets_open' => '"open"','tickets_pending' => '"pending"','tickets_solved'=> '"solved"'];

        foreach($status as $head => $status_value){
            $properties[$head]['total'] = self::countBytypeStatus($status_value,$alertMentionsIds);
        }


        return $properties; 

    }

    /**
     * return property view box.liveChat conversation
     * @param integer $alertId
     * @param integer $resourceId
     * @param string $term
     * @return $properties with properties record
     */
    public static function setBoxPropertiesLiveChatConversation($alertId,$resourceId,$term){
        $where = ['alertId' => $alertId,'resourcesId' => $resourceId];
        if($term != ""){
            $where['term_searched'] = $term;
        }

        $properties = self::getPropertyBoxByResourceName('Live Chat Conversations');
        $db = \Yii::$app->db;
        $duration = 5;
        
        $alertMentionsIds = $db->cache(function ($db) use ($where) {
            $ids =\app\models\AlertsMencions::find()->select(['id','alertId'])->where($where)->asArray()->all();
            return array_keys(\yii\helpers\ArrayHelper::map($ids,'id','alertId'));
        },$duration); 

        $expression = new Expression("`mention_data`->'$.event_id' AS eventId");
        // count number tickets
        // SELECT `mention_data`->'$.event_id' AS eventId FROM `mentions` where alert_mentionId = 9 GROUP BY `eventId` DESC
        $chatsCount = (new \yii\db\Query())
            ->cache($duration)
            ->select($expression)
            ->from('mentions')
            ->where(['alert_mentionId' => $alertMentionsIds])
            ->groupBy(['eventId'])
            ->count();
        
        $properties['chat_count']['total'] = $chatsCount;

        return $properties; 
    }
    /**
     * return count by status ticket
     * @param string $status
     * @param array $alertMentionsIds
     * @return $ticketCountStatus  by status
     */
    public static function countBytypeStatus($status, $alertMentionsIds)
    {
        // SELECT `mention_data`->'$.id' AS ticketId FROM `mentions` WHERE JSON_CONTAINS(mention_data,'"solved"','$.status') and alert_mentionId = 5 GROUP by ticketId
        $expression = new Expression("`mention_data`->'$.id' AS ticketId");
        $expressionWhere = new Expression("JSON_CONTAINS(mention_data,'{$status}','$.status')");

        $ticketCountStatus = (new \yii\db\Query())
            ->cache(5)
            ->select($expression)
            ->from('mentions')
            ->where($expressionWhere)
            ->andWhere(['alert_mentionId' => $alertMentionsIds])
            ->groupBy(['ticketId'])
            ->count();

        return $ticketCountStatus;    
    }

    /**
     * return group properties for view
     * @param string $resourceName
     * @return array $properties[$resourceName]
     */
    public static function getPropertyBoxByResourceName($resourceName){
        $properties = [
            'Twitter' => [
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
            ],
            'Live Chat' => [
                'tickets_open' => [
                    'id' => random_int(100, 999),
                    'total' => 0,
                    'background_color' => 'info-box-icon bg-danger elevation-1',
                    'title' => 'Total Tickets Abiertos',
                    'icon' => 'glyphicon glyphicon-eye-open'
                ],
                'tickets_pending' => [
                    'id' => random_int(100, 999),
                    'total' => 0,
                    'background_color' => 'info-box-icon bg-warning elevation-1',
                    'title' => 'Total Tickets Pendientes',
                    'icon' => 'glyphicon glyphicon-warning-sign'
                ],
                'tickets_solved' => [
                    'id' => random_int(100, 999),
                    'total' => 0,
                    'background_color' => 'info-box-icon bg-success elevation-1',
                    'title' => 'Total Tickets Solventados',
                    'icon' => 'glyphicon glyphicon-eye-close'
                ],
                'tickets_count' => [
                    'id' => random_int(100, 999),
                    'total' => 0,
                    'background_color' => 'info-box-icon bg-default elevation-1',
                    'title' => 'Total Tickets',
                    'icon' => 'glyphicon glyphicon-stats'
                ],
            ],
            'Live Chat Conversations' => [
                'chat_count' => [
                    'id' => random_int(100, 999),
                    'total' => 0,
                    'background_color' => 'info-box-icon bg-default elevation-1',
                    'title' => 'Total Chats',
                    'icon' => 'glyphicon glyphicon-comment'
                ],
            ]

        ];
        
        return $properties[$resourceName];
    }
}

?>