<?php

namespace app\models\search;

use yii\helpers\ArrayHelper;
use yii\httpclient\Client;

/**
 * InstagramSearch represents the model behind the search form of `app\models\api\InstagramApi`.
 */
class InstagramSearch 
{
    public $alertId;
    public $resourceId;
    public $data = [];
    public $isDictionaries = false;
    public $isBoolean = false;


    /**
     * [load load in to local variables]
     * @param  [array] $params [product [feed]]
     * @return [boolean]
     */
    public function load($data){
        if(empty($data)){
           return false;     
        }
        $this->resourceId    = \app\helpers\AlertMentionsHelper::getResourceIdByName('Instagram Comments');
        $this->isDictionaries = \app\helpers\AlertMentionsHelper::isAlertHaveDictionaries($this->alertId);
        
        
        $this->data = current($data);
        unset($data);
        return (count($this->data)) ? true : false;
    }


    /**
     * methodh applied depends of type search
     *
     *
     * @return boolean status
     */
    public function search()
    {   
        // if doesnt dictionaries and doesnt boolean
        if(!$this->isDictionaries && !$this->isBoolean){
            // save all data
            $mentions = $this->data;
            $search = $this->saveMentions($mentions);
            return $search;
        }

        // if  dictionaries and  boolean
        if($this->isDictionaries && $this->isBoolean){
            // init search
            echo "boolean and dictionaries \n";
            // retur something
        }

        // if  dictionaries and  !boolean
        if($this->isDictionaries && !$this->isBoolean){
            // init search
            $mentions = $this->data;
            $data = $this->searchDataByDictionary($mentions);

            $search = $this->saveMentions($data);
            return $search;
        }

        // if  !dictionaries and  boolean
        if(!$this->isDictionaries && $this->isBoolean){
            // init search
            echo "only boolean \n";
            // retur something
        }

    }

    /**
     * [saveMentions save  mentions or update]
     * @param  [array] $data [array]
     * @return [boolean]
     */
    private function saveMentions($data){
        $error = [];

        if(!is_null($data)){
            foreach($data as $product => $posts){
                for($p=0; $p < sizeof($posts); $p++){
                    $alertsMencions = $this->findAlertsMencionsByProducts($product,$posts[$p]['id']);
                    $permalink = $posts[$p]['permalink'];
                    
                    if(!is_null($alertsMencions) && isset($posts[$p]['comments'])){
                       if(count($posts[$p]['comments'])){
                            for($c = 0; $c < sizeof($posts[$p]['comments']); $c++){
                                
                                    // var_dump($posts[$p]['comments'][$c]);
                                    //$posts[$p]['comments'][$c]['permalink'] = $permalink;
                                    $this->savePropertyMentions($posts[$p]['comments'][$c],$alertsMencions,$permalink);
                                
                                    if(ArrayHelper::keyExists('replies', $posts[$p]['comments'][$c], false)){
                                        if(count($data[$product][$p]['comments'][$c]['replies']['data'])){
                                            $replies = $data[$product][$p]['comments'][$c]['replies']['data'];
                                            for($r = 0; $r < sizeof($replies); $r++ ){
                                                $this->savePropertyMentions($replies[$r],$alertsMencions,$permalink);
                                            }    
                                        }   
                                    }    
                            }// end loop comments
                       }
                    }// end if is_null alertsMencionsModel
                }
            }// end foreach data
        } // end if null
        return (empty($error)) ? true : false;
    }
    /**
     * [savePropertyMentions save  mentions ]
     * @param  [array] $comment [array comment prperties]
     * @param  [int] $alertsMencion [id alertsMencion]
     * @return [boolean]
     */
    private function savePropertyMentions($comment,$alertsMencion,$permalink = null){
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            // save user
            $user_data = [];
            $username = $comment['username'];
            //$user_response = $this->_getUser($username);
            
            
            
            if(!is_null($username)){
                if (\app\models\UsersMentions::find()->where(['screen_name' => $username])->exists()) {
                    // call user
                    $userMentions = \app\models\UsersMentions::findOne(['screen_name' => $username]);
                    // update data user is exists
                    // $user_data['followers_count'] = $user_response->graphql->user->edge_followed_by->count;
                    // $user_data['following_count'] = $user_response->graphql->user->edge_follow->count;
                    // $userMentions->user_data = $user_data;
                } else {
                    // $user_data['followers_count'] = $user_response->graphql->user->edge_followed_by->count;
                    // $user_data['following_count'] = $user_response->graphql->user->edge_follow->count;
                    // new register user
                    $userMentions =  new \app\models\UsersMentions();
                    $userMentions->user_uuid = $comment['id'];
                    // set name
                    // $name = \app\helpers\StringHelper::remove_emoji($user_response->graphql->user->full_name);
                    // $userMentions->name = (\app\helpers\StringHelper::isEmpty($name)) ? $username : $name;
                    // $userMentions->screen_name = $user_response->graphql->user->username;
                    $userMentions->name = $comment['username'];
                    $userMentions->screen_name = $comment['username'];
                    $userMentions->user_data = $user_data;
                    //$userMentions->message = \app\helpers\StringHelper::ensureRightPoints(\app\helpers\StringHelper::substring($user_response->graphql->user->biography,0,385));
                    $userMentions->profile_image_url = "https://www.instagram.com/{$username}/";
    
                }
                if(!$userMentions->save()){ throw new \Exception('Error user mentions Save');}

            }else{
                
                $userMentions = \app\models\UsersMentions::findOne(['screen_name' => $username]);
            }
            
            
            unset($user_data);
            // save mentions
            $mention_data['like_count'] = $comment['like_count'];

            // set params for search
            $alertsMentionsIds = \app\helpers\AlertMentionsHelper::getAlertsMentionsIdsByAlertIdAndResourcesIds($this->alertId,$this->resourceId);
            if(\app\models\Mentions::find()->where(
                [
                    'alert_mentionId' => $alertsMentionsIds,
                    'origin_id' => $userMentions->id,
                    'social_id' => $comment['id']
                ])->exists()){
                
                $mention = \app\models\Mentions::find()->where(
                    [
                        'origin_id' => $userMention->id,
                        'social_id' => $comment['id']
                    ]
                )->one();    

            }else{
                $mention = new \app\models\Mentions();
                $mention->alert_mentionId = $alertsMencion->id;
                $mention->origin_id = $userMentions->id;
                $mention->social_id = $comment['id'];
                $mention->created_time = \app\helpers\DateHelper::asTimestamp($comment['timestamp']);
                $mention->mention_data = $mention_data;
                $mention->message = $comment['text'];
                $mention->message_markup = (isset($comment['message_markup'])) ? $comment['message_markup'] : $comment['text'];
                $mention->url = $permalink;
               // $mention->domain_url = (!is_null($mention->url)) ? \app\helpers\StringHelper::getDomain($mention->url): null;
                // most repeated words
                \app\helpers\StringHelper::saveOrUpdatedCommonWords($mention,$alertsMencion);
                

            }
            unset($mention_data);
            if(!$mention->save()){ throw new \Exception('Error mentions Save');}
            
            // keywords mentions

            // if words find it
            if(ArrayHelper::keyExists('wordsId', $comment, false)){
                $wordIds = $comment['wordsId'];
                // save Keywords Mentions 
                if(\app\models\KeywordsMentions::find()->where(['mentionId'=> $mention->id])->exists()){
                    \app\models\KeywordsMentions::deleteAll('mentionId = '.$mention->id);
                }
        
                foreach($wordIds as $idwords => $count){
                    for($c = 0; $c < $count; $c++){
                        $model = new \app\models\KeywordsMentions();
                        $model->keywordId = $idwords;
                        $model->mentionId = $mention->id;
                        $model->save();
                    }
                }
                unset($wordIds);
                
            }
            else{
                // in case update in alert
                if(\app\models\KeywordsMentions::find()->where(['mentionId' => $mention->id])->exists()){
                    \app\models\KeywordsMentions::deleteAll('mentionId = '.$mention->id);
                }
                    
            }
            
            $transaction->commit();

        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

     /**
     * [searchDataByDictionary search keywords in the feed]
     * @param  [array] $mentions 
     * @return [array] [$mentions]
     */
    private function searchDataByDictionary($mentions){
        $words = \app\helpers\AlertMentionsHelper::getDictionariesWords($this->alertId);


        foreach($mentions as $product => $feeds){
            for($f = 0; $f < sizeof($feeds); $f++){
                if(ArrayHelper::keyExists('comments', $feeds[$f], false) && !empty($feeds[$f]['comments'])){
                    for($c = 0; $c <  sizeof($feeds[$f]['comments']); $c++){
                        $wordsId = [];
                        for($w = 0; $w < sizeof($words); $w++){
                            $sentence = \app\helpers\StringHelper::lowercase($mentions[$product][$f]['comments'][$c]['message_markup']);
                            $word = \app\helpers\StringHelper::lowercase($words[$w]['name']);
                            $containsCount = \app\helpers\StringHelper::containsCountIncaseSensitive($sentence, $word);
                            if($containsCount){
                                $wordsId[$words[$w]['id']] = $containsCount;
                                $mentions[$product][$f]['comments'][$c]['message_markup']  = \app\helpers\StringHelper::replaceIncaseSensitive($sentence,$word,"<strong>{$word}</strong>");
                            }// end if containsCount
                        }// end loop words
                        if(ArrayHelper::keyExists('replies', $mentions[$product][$f]['comments'][$c], false) ){
                            if(count($mentions[$product][$f]['comments'][$c]['replies']['data'])){
                                $replies = $mentions[$product][$f]['comments'][$c]['replies']['data'];
                                for($r = 0; $r < sizeof($replies); $r++){
                                    $wordsIdReplies = [];
                                    for($w = 0; $w < sizeof($words); $w++){
                                        if(isset($mentions[$product][$f]['comments'][$c]['replies']['data'][$r]['message_markup'])){
                                            $sentence_replies = \app\helpers\StringHelper::lowercase($mentions[$product][$f]['comments'][$c]['replies']['data'][$r]['message_markup']);
                                            $containsCount = \app\helpers\StringHelper::containsCountIncaseSensitive($sentence_replies, $word);
                                            if($containsCount){
                                                $wordsIdReplies[$words[$w]['id']] = $containsCount;
                                                $word_replies = $words[$w]['name'];
                                                $mentions[$product][$f]['comments'][$c]['replies']['data'][$r]['message_markup']  = \app\helpers\StringHelper::replaceIncaseSensitive($sentence_replies,$word_replies,"<strong>{$word_replies}</strong>");
                                                $mentions[$product][$f]['comments'][$c]['replies']['data'][$r]['wordsId'] = $wordsIdReplies;
                                                array_push($mentions[$product][$f]['comments'],$mentions[$product][$f]['comments'][$c]['replies']['data'][$r]);
                                            }
                        
                                        }
                                    }// end loop words
                                    if(empty($wordsIdReplies)){
                                        unset($mentions[$product][$f]['comments'][$c]['replies']['data'][$r]);
                                    }
                                } // end loop replies
                                $mentions[$product][$f]['comments'][$c]['replies']['data'] = array_values($mentions[$product][$f]['comments'][$c]['replies']['data']);
                            } // end if count replies data
                        } // end if replies
                        if(!empty($wordsId)){
                            $mentions[$product][$f]['comments'][$c]['wordsId'] = $wordsId;
                        }else{
                            unset($mentions[$product][$f]['comments'][$c]);
                        }
                    }// end loop comments
                    $mentions[$product][$f]['comments'] = array_values($mentions[$product][$f]['comments']);
                }// end if keyExists && !empty
            }// end loop feeds
        }// for each
       // var_dump($mentions);
        return $mentions;
    }

    /**
     * Finds the AlertsMencions model based on product key value.
     * @param string $product
     * @return AlertsMencions the loaded model
     */
    private function _findAlertsMencions($product,$publication_id)
    {

        $alertsMencions =  \app\models\AlertsMencions::find()->where([
            'alertId'        => $this->alertId,
            'resourcesId'    =>  $this->resourcesId,
           // 'condition'      =>  'ACTIVE',
            'type'           =>  'comments Instagram',
            'term_searched'  =>  $product,
            'publication_id' =>  $publication_id,
        ])
        ->select('id')->one();

        return $alertsMencions;

    }

    /**
     * Finds the AlertsMencions model based on product key value.
     * @param string $product
     * @return AlertsMencions the loaded model
     */
    private function findAlertsMencionsByProducts($product,$publication_id)
    {

        $alertsMencions = (new \yii\db\Query())
        ->select(['id','publication_id'])
        ->from(\app\models\AlertsMencions::tableName())
        ->where(
            [
                'alertId'        => $this->alertId,
                'resourcesId'    =>  $this->resourceId,
                // 'condition'      =>  'ACTIVE',
                'type'           =>  'comments Instagram',
                'term_searched'  =>  $product,
                'publication_id' =>  $publication_id,
            ])
        ->one();
        return ($alertsMencions) ? (object) $alertsMencions : null;

    }
    /**
     * [saveUserMencions save user porperties]
     * @param  [string] $username           [name username]
     */
    public function saveUserMencions($username){
        
        if(!\app\models\UsersMentions::find()->where( [ 'screen_name' => $username] )->exists()){
            $user_response = json_decode($this->_getUser($username),true);

            if(!is_null($user_response)){
                if(!is_null($user_response['graphql'])){


                    $user_data['followers_count'] = $user_response['graphql']['user']['edge_followed_by']['count'];
                    $user_data['following_count'] = $user_response['graphql']['user']['edge_follow']['count'];

                    $name = \app\helpers\StringHelper::remove_emoji($user_response['graphql']['user']['full_name']);

                    $id = $user_response['graphql']['user']['id'];
                    $permalink = "https://www.instagram.com/{$username}/";
                    $screen_name = $username;
                    $name = (\app\helpers\StringHelper::isEmpty($name)) ? $username : $name;
                    
                    $message = $user_response['graphql']['user']['biography'];
                    $short_message = \app\helpers\StringHelper::ensureRightPoints(\app\helpers\StringHelper::substring($message,0,385));
                    $origin = \app\helpers\MentionsHelper::saveUserMencions(
                        [
                            'user_uuid' => $id
                        ],
                        [
                            'name'              => \app\helpers\StringHelper::remove_emoji($name),
                            'screen_name'       => $screen_name,
                            'user_data'         => $user_data,
                            'message'           => \app\helpers\StringHelper::remove_emoji($short_message),
                            'profile_image_url' => $permalink,
                        ]
                    );

                } // end if is_null graphql
                

            }// end if is_null user_response
        }else{

            $origin = \app\models\UsersMentions::findOne(['screen_name' => $username]);

        }

        return $origin;
        
    }

    /**
     * [saveMencions save or update mentions]
     * @param  [array] $tweets           [tweet]
     * @param  [int] $alertsMencionsId   [alert mentions]
     * @param  [int] $originId           [id user ]
     * @return [obj]                     [model mentions]
     */
    private function saveMencions($comment,$alertsMencionsId,$originId){

        $url          = (!empty($comment['permalink'])) ? $comment['permalink']: null;
        $domain_url   = (!is_null($url)) ? \app\helpers\StringHelper::getDomain($url): null;
        $social_id    = $comment['id'];
        $created_time = \app\helpers\DateHelper::asTimestamp($comment['timestamp']);
        $message      = $comment['text'];
        $message_markup = $comment['message_markup'];

        $mention_data['like_count'] = $comment['like_count'];

        $mention = \app\helpers\MentionsHelper::saveMencions(
            [
                'alert_mentionId' => $alertsMencionsId,
                'social_id'       => $social_id ,
                'origin_id'       => $originId
            ],
            [
                'created_time'   => $created_time,
                'mention_data'   => $mention_data,
                'subject'        => '',
                'message'        => $message,
                'message_markup' => $message_markup,
                'url'            => $url ,
                'domain_url'     => $domain_url ,
                'location'       => '-' ,
                
            ]
        );

        return $mention;

    }
    /**
     * [saveReplies save replies]
     * @param  [array] $comment   [coments of the post ]
     * @param  [int] $alertsMencionsId   [id alertsMencions]
     * @param  [originId] $originId   [id user]
     */
    private function saveReplies($comment,$alertsMencionsId,$originId){

    }
    /**
     * [saveKeywordsMentions save or update  KeywordsMentions]
     * @param  [array] $wordIds   [array wordId => total count in the sentece ]
     * @param  [int] $mentionId   [id mention]
     */
    private function saveKeywordsMentions($wordIds,$mentionId){

        if(\app\models\KeywordsMentions::find()->where(['mentionId'=> $mentionId])->exists()){
            \app\models\KeywordsMentions::deleteAll('mentionId = '.$mentionId);
        }

        foreach($wordIds as $idwords => $count){
            for($c = 0; $c < $count; $c++){
                $model = new \app\models\KeywordsMentions();
                $model->keywordId = $idwords;
                $model->mentionId = $mentionId;
                $model->save();
            }
        }

    }

    /**
     * [_getClient return client http request]
     * @return [obj] [return object client]
     */
    private function _getUser($username){
        
        $client = new \yii\httpclient\Client([
            'parsers' => [
                // configure options of the JsonParser, parse JSON as objects
                \yii\httpclient\Client::FORMAT_JSON => [
                    'class' => 'yii\httpclient\JsonParser',
                    'asArray' => false,
                ]
            ],
        ]);
        $response = $client->createRequest()
            ->setMethod('GET')
            ->setUrl("https://www.instagram.com/{$username}/?__a=1")
            ->send();
        if ($response->isOk) {
            return $response->data;
        }
        return null;
    }


}
