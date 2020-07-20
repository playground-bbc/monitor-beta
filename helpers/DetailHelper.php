<?php
namespace app\helpers;

use yii;
use yii\helpers\Html;
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
    public static function setBoxPropertiesLiveChat($alertId,$resourceId,$term,$socialId){

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

        $mentionWhere = ['alert_mentionId' => $alertMentionsIds];
        if($socialId != ""){
            $mentionWhere['social_id'] = $socialId;
        } 

        $expression = new Expression("`mention_data`->'$.id' AS ticketId");
        // count number tickets
        // SELECT `mention_data`->'$.id' AS ticketId FROM `mentions` where alert_mentionId = 9 GROUP BY `ticketId` DESC
        $ticketCount = (new \yii\db\Query())
            ->cache($duration)
            ->select($expression)
            ->from('mentions')
            ->where($mentionWhere)
            ->groupBy(['ticketId'])
            ->count();

        $properties['tickets_count']['total'] = $ticketCount;    
        // count number tickets open
        $status = ['tickets_open' => '"open"','tickets_pending' => '"pending"','tickets_solved'=> '"solved"'];

        foreach($status as $head => $status_value){
            $properties[$head]['total'] = self::countBytypeStatus($status_value,$mentionWhere);
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
    public static function setBoxPropertiesLiveChatConversation($alertId,$resourceId,$term,$socialId = null){
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
        
        $mentionWhere = ['alert_mentionId' => $alertMentionsIds];
        if($socialId != ''){
            $mentionWhere['social_id'] = $socialId;
        }
        // count number tickets
        // SELECT `mention_data`->'$.event_id' AS eventId FROM `mentions` where alert_mentionId = 9 GROUP BY `eventId` DESC
        $chatsCount = (new \yii\db\Query())
            ->cache($duration)
            ->select($expression)
            ->from('mentions')
            ->where($mentionWhere)
            ->groupBy(['eventId'])
            ->count();
        
        $properties['chat_count']['total'] = $chatsCount;

        return $properties; 
    }
    /**
     * return property view box.Facebook Comments
     * @param integer $alertId
     * @param integer $resourceId
     * @param string $term
     * @param integer $feedId
     * @return $properties with properties record
     */
    public static function setBoxPropertiesFaceBookComments($alertId,$resourceId,$term,$feedId){
        $where = ['alertId' => $alertId,'resourcesId' => $resourceId];
        if($term != ""){
            $where['term_searched'] = $term;
        }
        if($feedId != ""){
            $where['publication_id'] = $feedId; 
        }

        $properties = self::getPropertyBoxByResourceName('Facebook Comments');
        $db = \Yii::$app->db;
        $duration = 5;

        $alertMentions = \app\models\AlertsMencions::find()->with(['mentions'])->where($where)->asArray()->all();
        for ($m=0; $m < sizeOf($alertMentions) ; $m++) { 
            if(count($alertMentions[$m]['mentions'])){
                // get total comments
                $properties['comments_count']['total'] += count($alertMentions[$m]['mentions']);
                // get total shares
                $mention_data = json_decode($alertMentions[$m]['mention_data'],true);
                if(isset($mention_data['shares'])){
                    $properties['shares_count']['total'] += $mention_data['shares']; 
                }
                if(isset($mention_data['reations'])){
                    $properties['likes_count']['total'] += (isset($mention_data['reations']['like'])) ? $mention_data['reations']['like']: 0; 
                    $properties['loves_count']['total'] += (isset($mention_data['reations']['love'])) ? $mention_data['reations']['love']: 0; 
                    $properties['wow_count']['total'] += (isset($mention_data['reations']['wow'])) ? $mention_data['reations']['wow']: 0; 
                    $properties['haha_count']['total'] += (isset($mention_data['reations']['haha'])) ? $mention_data['reations']['haha']: 0; 
                }
            }
        }
        return $properties; 
    }
    /**
     * return property view box.Facebook Messages
     * @param integer $alertId
     * @param integer $resourceId
     * @param string $term
     * @param integer $feedId
     * @return $properties with properties record
     */
    public static function setBoxPropertiesFaceBookMessages($alertId,$resourceId,$term,$feedId){
        $where = ['alertId' => $alertId,'resourcesId' => $resourceId];
        if($term != ""){
            $where['term_searched'] = $term;
        }
        if($feedId != ""){
            $where['publication_id'] = $feedId;
        }

        $properties = self::getPropertyBoxByResourceName('Facebook Messages');
        $db = \Yii::$app->db;
        $duration = 5;
        $alertMentions = \app\models\AlertsMencions::find()->with(['mentions'])->where($where)->asArray()->all();
        for ($m=0; $m < sizeOf($alertMentions) ; $m++) { 
            if(count($alertMentions[$m]['mentions'])){
                // get total messages
                $properties['inbox_count']['total'] += count($alertMentions[$m]['mentions']);
            }
        }
        return $properties; 
    }
    /**
     * return property view box.Instagram Comments
     * @param integer $alertId
     * @param integer $resourceId
     * @param string $term
     * @param integer $feedId
     * @return $properties with properties record
     */
    public static function setBoxPropertiesInstagramComments($alertId,$resourceId,$term,$feedId = null){
        $where = ['alertId' => $alertId,'resourcesId' => $resourceId];
        if($term != ""){
            $where['term_searched'] = $term;
        }
        if($feedId != ""){
            $where['publication_id'] = $feedId; 
        }


        $properties = self::getPropertyBoxByResourceName('Instagram Comments');
        $db = \Yii::$app->db;
        $duration = 5;
        $alertMentions = \app\models\AlertsMencions::find()->with(['mentions'])->where($where)->asArray()->all();
        for ($m=0; $m < sizeOf($alertMentions) ; $m++) { 
            if(count($alertMentions[$m]['mentions'])){
                // get total messages
                $properties['comments_count']['total'] += count($alertMentions[$m]['mentions']);
                // get total likes
                $mention_data = json_decode($alertMentions[$m]['mention_data'],true);
                $properties['likes_count']['total'] += (isset($mention_data['like_count'])) ? $mention_data['like_count']: 0; 
            }
        }
        return $properties; 
    }
    /**
     * return property view box.Paginas Wbes
     * @param integer $alertId
     * @param integer $resourceId
     * @param string $term
     * @return $properties with properties record
     */
    public static function setBoxPropertiesPaginasWebs($alertId,$resourceId,$term){
        $where = ['alertId' => $alertId,'resourcesId' => $resourceId];
        if($term != ""){
            $where['term_searched'] = $term;
        }

        $properties = self::getPropertyBoxByResourceName('Paginas Webs');
        $db = \Yii::$app->db;
        $duration = 5;
        $alertMentions = \app\models\AlertsMencions::find()->with(['mentions'])->where($where)->asArray()->all();
        for ($m=0; $m < sizeOf($alertMentions) ; $m++) { 
            if(count($alertMentions[$m]['mentions'])){
                // total web pages
                $properties['webpages_count']['total'] ++;
                // get total messages
                $properties['mention_count']['total'] += count($alertMentions[$m]['mentions']);
            }
        }
        return $properties; 
    }

    /**
     * return count by status ticket
     * @param string $status
     * @param array $alertMentionsIds
     * @return $ticketCountStatus  by status
     */
    public static function countBytypeStatus($status, $mentionWhere)
    {
        // SELECT `mention_data`->'$.id' AS ticketId FROM `mentions` WHERE JSON_CONTAINS(mention_data,'"solved"','$.status') and alert_mentionId = 5 GROUP by ticketId
        $expression = new Expression("`mention_data`->'$.id' AS ticketId");
        $expressionWhere = new Expression("JSON_CONTAINS(mention_data,'{$status}','$.status')");

        $ticketCountStatus = (new \yii\db\Query())
            ->cache(5)
            ->select($expression)
            ->from('mentions')
            ->where($expressionWhere)
            ->andWhere($mentionWhere)
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
                    'icon' => 'glyphicon glyphicon-retweet',
                    'attribute' => 'retweet_count',
                    'method' => 'sort'
                ],
                'favorite_count' => [
                    'id' => random_int(100, 999),
                    'total' => 0,
                    'background_color' => 'info-box-icon bg-danger elevation-1',
                    'title' => 'Total Favorites',
                    'icon' => 'glyphicon glyphicon-heart',
                    'attribute' => 'favorite_count',
                    'method' => 'sort'
                ],
                'tweets_count' => [
                    'id' => random_int(100, 999),
                    'total' => 0,
                    'background_color' => 'info-box-icon bg-default elevation-1',
                    'title' => 'Total Tweets',
                    'icon' => 'glyphicon glyphicon-stats',
                    'attribute' => '',
                    'method' => 'sort'
                ],
            ],
            'Live Chat' => [
                'tickets_open' => [
                    'id' => random_int(100, 999),
                    'total' => 0,
                    'background_color' => 'info-box-icon bg-danger elevation-1',
                    'title' => 'Total Tickets Abiertos',
                    'icon' => 'glyphicon glyphicon-eye-open',
                    'attribute' => ['status' => 'open'],
                    'method' => 'search'
                ],
                'tickets_pending' => [
                    'id' => random_int(100, 999),
                    'total' => 0,
                    'background_color' => 'info-box-icon bg-warning elevation-1',
                    'title' => 'Total Tickets Pendientes',
                    'icon' => 'glyphicon glyphicon-warning-sign',
                    'attribute' => ['status' => 'pending'],
                    'method' => 'search'
                ],
                'tickets_solved' => [
                    'id' => random_int(100, 999),
                    'total' => 0,
                    'background_color' => 'info-box-icon bg-success elevation-1',
                    'title' => 'Total Tickets Solventados',
                    'icon' => 'glyphicon glyphicon-eye-close',
                    'attribute' => ['status' => 'solved'],
                    'method' => 'search'
                ],
                'tickets_count' => [
                    'id' => random_int(100, 999),
                    'total' => 0,
                    'background_color' => 'info-box-icon bg-default elevation-1',
                    'title' => 'Total Tickets',
                    'icon' => 'glyphicon glyphicon-stats',
                    'attribute' => ['status' => ''],
                    'method' => 'search'
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
            ],
            'Facebook Comments' => [
                'comments_count'=>[
                    'id' => random_int(100, 999),
                    'total' => 0,
                    'background_color' => 'info-box-icon bg-default elevation-1',
                    'title' => 'Total Comentarios',
                    'icon' => 'glyphicon glyphicon-comment'
                ],
                'shares_count'=>[
                    'id' => random_int(100, 999),
                    'total' => 0,
                    'background_color' => 'info-box-icon bg-default elevation-1',
                    'title' => 'Total Compartidos',
                    'icon' => 'glyphicon glyphicon-share'
                ],
                'likes_count'=>[
                    'id' => random_int(100, 999),
                    'total' => 0,
                    'background_color' => 'info-box-icon bg-default elevation-1',
                    'title' => 'Total Likes',
                    'icon' => 'glyphicon glyphicon-thumbs-up'
                ],
                'loves_count'=>[
                    'id' => random_int(100, 999),
                    'total' => 0,
                    'background_color' => 'info-box-icon bg-default elevation-1',
                    'title' => 'Total Loves',
                    'icon' => 'glyphicon glyphicon-heart'
                ],
                'wow_count'=>[
                    'id' => random_int(100, 999),
                    'total' => 0,
                    'background_color' => 'info-box-icon bg-default elevation-1',
                    'title' => 'Total Wow',
                    'icon' => 'glyphicon glyphicon-user'
                ],
                'haha_count'=>[
                    'id' => random_int(100, 999),
                    'total' => 0,
                    'background_color' => 'info-box-icon bg-default elevation-1',
                    'title' => 'Total Haha',
                    'icon' => 'glyphicon glyphicon-user'
                ],
            ],
            'Facebook Messages' => [
                'inbox_count'=>[
                    'id' => random_int(100, 999),
                    'total' => 0,
                    'background_color' => 'info-box-icon bg-default elevation-1',
                    'title' => 'Total Inbox',
                    'icon' => 'glyphicon glyphicon-envelope'
                ],
            ],
            'Instagram Comments' => [
                'comments_count'=>[
                    'id' => random_int(100, 999),
                    'total' => 0,
                    'background_color' => 'info-box-icon bg-default elevation-1',
                    'title' => 'Total Comentarios',
                    'icon' => 'glyphicon glyphicon-comment'
                ],
                'likes_count'=>[
                    'id' => random_int(100, 999),
                    'total' => 0,
                    'background_color' => 'info-box-icon bg-default elevation-1',
                    'title' => 'Total Likes',
                    'icon' => 'glyphicon glyphicon-thumbs-up'
                ],
            ],
            'Paginas Webs' => [
                'mention_count'=>[
                    'id' => random_int(100, 999),
                    'total' => 0,
                    'background_color' => 'info-box-icon bg-default elevation-1',
                    'title' => 'Total Coincidencias',
                    'icon' => 'glyphicon glyphicon-ok'
                ],
                'webpages_count'=>[
                    'id' => random_int(100, 999),
                    'total' => 0,
                    'background_color' => 'info-box-icon bg-default elevation-1',
                    'title' => 'Total Paginas webs',
                    'icon' => 'socicon-internet'
                ],
            ]

        ];
        
        return $properties[$resourceName];
    }

    /**
     * return ticket on live chat on view detail
     * @param integer $alertId
     * @param integer $resourceId
     * @return string $term
     */
    public static function getTicketLiveChat($alertId,$resourceId,$term){
        $where = ['alertId' => $alertId,'resourcesId' => $resourceId,'term_searched' => $term];

        $db = \Yii::$app->db;
        $duration = 5;
        $alertMentions = \app\models\AlertsMencions::find()->with(['mentions'])->where($where)->asArray()->all();
        $data = [];
        
        for ($m=0; $m < sizeOf($alertMentions) ; $m++) { 
            if(count($alertMentions[$m]['mentions'])){
                // SELECT social_id,subject FROM `mentions` WHERE alert_mentionId=101 GROUP BY `social_id`
                $rows = (new \yii\db\Query())
                      ->select(['social_id','subject'])
                      ->from('mentions')
                      ->where(['alert_mentionId' => $alertMentions[$m]['id']])
                      ->groupBy(['social_id','subject'])
                      ->all();
                     
                if(count($rows)){
                    foreach($rows as $row){
                        array_push($data,['id' => $row['social_id'], 'text' => $row['subject']]);
                    }
                    
                }      
            }
        }

        return $data;

    }

    /**
     * return Chats on live chat on view detail
     * @param integer $alertId
     * @param integer $resourceId
     * @return string $term
     */
    public static function getChatsLiveChat($alertId,$resourceId,$term){
        $where = ['alertId' => $alertId,'resourcesId' => $resourceId,'term_searched' => $term];

        $db = \Yii::$app->db;
        $duration = 5;
        $alertMentions = \app\models\AlertsMencions::find()->with(['mentions'])->where($where)->asArray()->all();
        $data = [];
        
        for ($m=0; $m < sizeOf($alertMentions) ; $m++) { 
            if(count($alertMentions[$m]['mentions'])){
                // SELECT mentions.social_id,users_mentions.name FROM `mentions` 
                //JOIN users_mentions on mentions.origin_id = users_mentions.id 
                //WHERE users_mentions.name <> 'Cliente' 
                // AND alert_mentionId = 217 GROUP BY social_id ORDER BY `created_time` ASC
                $rows = (new \yii\db\Query())
                      ->select(['mentions.social_id','users_mentions.name'])
                      ->from('mentions')
                      ->join('JOIN','users_mentions', 'mentions.origin_id = users_mentions.id')
                      ->where(['alert_mentionId' => $alertMentions[$m]['id']])
                      ->andWhere(['<>', 'users_mentions.name', 'Cliente'])
                      ->groupBy(['social_id','users_mentions.name'])
                      ->all();
               
                if(count($rows)){
                    foreach($rows as $index => $row){
                        $numeric_index = $index + 1;
                        array_push($data,['id' => $row['social_id'], 'text' => "#{$numeric_index} Atendido por: {$row['name']}"]);
                    }
                    
                }      
            }
        }

        return $data;

    }
    /**
     * return post on facebook comments on view detail
     * @param integer $alertId
     * @param integer $resourceId
     * @return string $term
     */
    public static function getPostsFaceBookComments($alertId,$resourceId,$term){
        $where = ['alertId' => $alertId,'resourcesId' => $resourceId,'term_searched' => $term];

        $db = \Yii::$app->db;
        $duration = 5;
        $alertMentions = \app\models\AlertsMencions::find()->with(['mentions'])->where($where)->asArray()->all();
        $data = [];

        for ($m=0; $m < sizeOf($alertMentions) ; $m++) { 
            if(count($alertMentions[$m]['mentions'])){
                $title = \app\helpers\StringHelper::substring($alertMentions[$m]['title'],0,60)."....";
                array_push($data,['id' => $alertMentions[$m]['publication_id'], 'text' => $title]);     
            }
        }

        return $data;
    }

    /**
     * return post on facebook comments on view detail
     * @param integer $alertId
     * @param integer $resourceId
     * @return string $term
     */
    public static function getInboxFaceBookComments($alertId,$resourceId,$term){
        $where = ['alertId' => $alertId,'resourcesId' => $resourceId,'term_searched' => $term];

        //$alertsMentionsIds = \app\models\AlertsMencions::find()->select('id')->where($where)->asArray()->all();
        $alertsMentions = \app\models\AlertsMencions::find()->with('mentions.origin')->where($where)->asArray()->all();
        //$ids = \yii\helpers\ArrayHelper::getColumn($alertsMentionsIds, 'id');
        $data = [];
        
        // SELECT alerts_mencions.publication_id,users_mentions.name FROM `mentions` 
        //JOIN alerts_mencions on alerts_mencions.id = mentions.alert_mentionId 
        //JOIN users_mentions on mentions.origin_id = users_mentions.id 
        //WHERE users_mentions.name <> 'Mundo LG' 
        //AND mentions.alert_mentionId = 333 GROUP BY publication_id ORDER BY `created_time` ASC

        for($a = 0; $a < sizeOf($alertsMentions); $a ++){
            if(count($alertsMentions[$a]['mentions'])){
                
                $rows = (new \yii\db\Query())
                      ->select(['users_mentions.name'])
                      ->from('mentions')
                      ->join('JOIN','users_mentions', 'mentions.origin_id = users_mentions.id')
                      ->where(['alert_mentionId' => $alertsMentions[$a]['id']])
                      ->andWhere(['<>', 'users_mentions.name', 'Mundo LG'])
                      ->groupBy(['social_id','users_mentions.name'])
                      ->all();
                    
                if(count($rows)){
                    foreach($rows as $index => $row){
                        if($row['name'] != 'Mundo LG'){
                            array_push($data,['id' => $alertsMentions[$a]['publication_id'], 'text' => "# Usuario: {$row['name']}"]);
                        }
                    }
                    
                }
            }
        }
          
       return $data;
    }

    /**
     * return group columns for detail grid index detail
     * @param string $resourceName
     * @return array $columns
     */
    public static function setGridDetailColumnsOnDetailView($model,$resource){
        
        $columns = [
            [
                'label' => Yii::t('app','Estado'),
                'format'    => 'raw',
                'attribute' => 'status',
                'value' => function($model) {
                    return ($model->status) ? 'Active' : 'Inactive';
                }
            ],
            [
                'label' => Yii::t('app','Recurso'),
                'format'    => 'raw',
                'value' => function($model) use($resource) {
                    return Html::encode($resource->name);
                }
            ],
            [
                'label' => Yii::t('app','Terminos a Buscar'),
                'format'    => 'raw',
                'value' => \kartik\select2\Select2::widget([
                    'name' => 'products',
                    'size' => \kartik\select2\Select2::SMALL,
                    'hideSearch' => false,
                    'data' => $model->termsFind,
                    'options' => ['placeholder' => 'Terminos...'],
                    'pluginOptions' => [
                        'allowClear' => true
                    ],
                ]),

            ],
        ];


        if($resource->name == "Live Chat" || $resource->name == "Live Chat Conversations"){
            $place_holder = ($resource->name == "Live Chat") ? 'Tickets a Filtrar...' : 'Conversaciones a Filtrar...';    
            $columnTicket = [
                'label' => Yii::t('app','Tickets a Buscar'),
                'format'    => 'raw',
                'value' => \kartik\select2\Select2::widget([
                    'id' => 'depend_select',
                    'name' => 'ticket',
                    'size' => \kartik\select2\Select2::SMALL,
                    'hideSearch' => false,
                    'data' => [],
                    'options' => ['placeholder' => $place_holder],
                    'pluginOptions' => [
                        'allowClear' => true
                    ],
                ]),
            ];
            array_push($columns,$columnTicket);
        }

        if($resource->name == "Facebook Comments" || $resource->name == "Instagram Comments" ){
            $columnComment = [
                'label' => Yii::t('app','Posts a Buscar'),
                'format'    => 'raw',
                'value' => \kartik\select2\Select2::widget([
                    'id' => 'depend_select',
                    'name' => 'post',
                    'size' => \kartik\select2\Select2::SMALL,
                    'hideSearch' => false,
                    'data' => [],
                    'options' => ['placeholder' => 'Posts a Filtrar...'],
                    'pluginOptions' => [
                        'allowClear' => true
                    ],
                ]),
            ];
            array_push($columns,$columnComment);
        }

        if($resource->name == "Facebook Messages"){
            $columnComment = [
                'label' => Yii::t('app','Conversaciones a Buscar'),
                'format'    => 'raw',
                'value' => \kartik\select2\Select2::widget([
                    'id' => 'depend_select',
                    'name' => 'post',
                    'size' => \kartik\select2\Select2::SMALL,
                    'hideSearch' => false,
                    'data' => [],
                    'options' => ['placeholder' => 'Inboxs a Filtrar...'],
                    'pluginOptions' => [
                        'allowClear' => true
                    ],
                ]),
            ];
            array_push($columns,$columnComment);
        }

        return $columns;
    }
    /**
     * return group columns for mentions grid index detail
     */
    public static function setGridMentionsColumnsOnDetailView($resourceName,$searchModel){

        $columns = [
            [
                'label' => Yii::t('app','Fecha'),
                'headerOptions' => ['style' => 'width:25%'],
                'attribute' => 'created_time',
                'format' => 'raw',
                'value' => function($model){
                    return \Yii::$app->formatter->asDate($model['created_time'], 'yyyy-MM-dd');
                },
                'filter' => \kartik\date\DatePicker::widget([
                    'name' => 'MentionSearch[created_time]',
                    'type' => \kartik\date\DatePicker::TYPE_COMPONENT_APPEND,
                    'value' => $searchModel['created_time'],
                  // 'layout' => $layout2,
                    'pluginOptions' => [
                        'autoclose' => true,
                        'format' => 'yyyy/mm/dd',
                    ]
                ]),
            ],
        ];

        if($resourceName == 'Twitter'){
            array_push($columns,
                self::composeColum("Nombre","name","raw",function($model){
                    return \yii\helpers\Html::encode($model['name']);
                }),
                
                self::composeColum("Username","screen_name","raw",function($model){
                    return \yii\helpers\Html::encode($model['screen_name']);
                }),
                
                self::composeColum("Mencion","message_markup","raw",function($model){
                    return $model['message_markup'];
                }),
                
                self::composeColum("Total Retweet","retweet_count","raw",function($model){
                    return \yii\helpers\Html::encode($model['retweet_count']);
                },['style'=>'padding:0px 0px 0px 30px;vertical-align: middle;']),

                self::composeColum("Total Favoritos","favorite_count","raw",function($model){
                    return \yii\helpers\Html::encode($model['favorite_count']);
                },['style'=>'padding:0px 0px 0px 30px;vertical-align: middle;']),

                self::composeColum("Url","","raw",function($model){
                    return \yii\helpers\Html::a('link',$model['url'],['target'=>'_blank', 'data-pjax'=>"0"]);
                })
            );
        }

        if($resourceName == 'Live Chat'){
            array_push($columns,
                self::composeColum("Nombre","name","raw",function($model){
                    $type_user = '';
                    if(isset($model['user_mention']['type'])){
                        $type_user = "({$model['user_mention']['type']})";
                    }
                    $name = "{$model['name']} {$type_user}";
                    return \yii\helpers\Html::encode($name);
                },['style' => 'width: 10%;min-width: 20px']), 
                
                self::composeColum("Mencion","message_markup","raw",function($model){
                    return $model['message_markup'];
                }),

                self::composeColum("Url Retail","","raw",function($model){
                    $url = '-';
                    if(!is_null($model['url'])){
                        $url = \yii\helpers\Html::a('link',$model['url'],['target'=>'_blank', 'data-pjax'=>"0"]);  
                    }
                    return $url;
                })
        
            );
        }

        if($resourceName == 'Live Chat Conversations'){
            array_push($columns,
                self::composeColum("Nombre","name","raw",function($model){
                    return \yii\helpers\Html::encode($model['name']);
                },['style' => 'width: 10%;min-width: 20px']),

                self::composeColum("Mencion","message_markup","raw",function($model){
                    return $model['message_markup'];
                }),

                self::composeColum("Ciudad","user_data","raw",function($model){
                    $user_data = json_decode($model['user_data'],true);
                    $city = (isset($user_data['geo']['city'])) ? $user_data['geo']['city'] : '-'; 
                    return \yii\helpers\Html::encode($city, $doubleEncode = true);
                }),

                self::composeColum("Region","user_data","raw",function($model){
                    $user_data = json_decode($model['user_data'],true);
                    $town = (isset($user_data['geo']['region'])) ? $user_data['geo']['region'] : '-'; 
                    return \yii\helpers\Html::encode($town, $doubleEncode = true);
                }),

                self::composeColum("Url Retail","","raw",function($model){
                    $url = '-';
                    if(!is_null($model['url'])){
                        $url = \yii\helpers\Html::a('link',$model['url'],['target'=>'_blank', 'data-pjax'=>"0"]);  
                    }
                    return $url;
                })
            );
        }

        if($resourceName == 'Facebook Comments'){
            array_push($columns,
                self::composeColum("Mencion","message_markup","raw",function($model){
                    return $model['message_markup'];
                }),

                self::composeColum("Url Comentario","","raw",function($model){
                    $url = '-';
                    if(!is_null($model['url'])){
                        $url = \yii\helpers\Html::a('link',$model['url'],['target'=>'_blank', 'data-pjax'=>"0"]);  
                    }
                    return $url;
                })
            );
        }

        if($resourceName == 'Facebook Messages'){
            array_push($columns,
                self::composeColum("Nombre","Name","raw",function($model){
                    return \yii\helpers\Html::encode($model['name']);
                }),

                self::composeColum("Email FaceBook","Email","raw",function($model){
                    $user_data = \yii\helpers\Json::decode($model['user_data'], $asArray = true);
                    $email = (isset($user_data['email'])) ? $user_data['email'] : "-";
                    return \yii\helpers\Html::encode($email);
                }),

                self::composeColum("Mencion","message_markup","raw",function($model){
                    return $model['message_markup'];
                }),

                self::composeColum("Url Inbox","","raw",function($model){
                    $url = '-';
                    if(!is_null($model['url'])){
                        $url = \yii\helpers\Html::a('link',$model['url'],['target'=>'_blank', 'data-pjax'=>"0"]);  
                    }
                    return $url;
                })
            );

        }

        if($resourceName == 'Instagram Comments'){
            array_push($columns,

                self::composeColum("Nombre","name","raw",function($model){
                    return \yii\helpers\Html::encode($model['name']);
                }),
                
                self::composeColum("Username","screen_name","raw",function($model){
                    return \yii\helpers\Html::encode($model['screen_name']);
                }),

                self::composeColum("Mencion","message_markup","raw",function($model){
                    return $model['message_markup'];
                }),

                self::composeColum("Url Comentario","","raw",function($model){
                    $url = '-';
                    if(!is_null($model['url'])){
                        $url = \yii\helpers\Html::a('link',$model['url'],['target'=>'_blank', 'data-pjax'=>"0"]);  
                    }
                    return $url;
                })
            );
        }

     
        return $columns;
    }
    /** 
     * Compose column for gridview
     * @param string $label
     * @param string $attribute
     * @param function $format
     * @param array contentOptions
     * 
     */
    public static function composeColum($label,$attribute,$format ="raw",$value,$contentOptions = null){
        
        $column = [
            'label' => Yii::t('app',$label),
            'attribute' => $attribute,
            'format' => $format,
            'value' => $value,
        ];
        
        if(!is_null($contentOptions)){
            $column['contentOptions'] = $contentOptions;
        }

        return $column;
    }
    
}

?>