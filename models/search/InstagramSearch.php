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
    public $resourcesId;
    public $data = [];
    public $isDictionaries = false;
    public $isBoolean = false;


    /**
     * [load load in to local variables]
     * @param  [array] $params [product [feed]]
     * @return [boolean]
     */
    public function load($params){
        if(empty($params)){
           return false;     
        }
        $this->alertId        = ArrayHelper::getValue($params, 0);
        $this->resourcesId    = $this->_setResourceId();
        $this->isDictionaries = $this->_isDictionaries();
        
        
        // is boolean
        // loop data
        for($p = 1; $p < sizeof($params); $p++){
            // loop with json file
            for($j = 0; $j < sizeof($params[$p]); $j++){
                $products = $params[$p][$j][0];
                // for each product
                foreach($products as $product => $datos){
                   // for each feed 
                   for($d = 0; $d < sizeof($datos); $d++){
                        if(!ArrayHelper::keyExists($product, $this->data, false)){
                            $this->data[$product] = [];
                        }
                        if(!in_array($datos[$d], $this->data[$product])){
                            $this->data[$product] [] = $datos[$d];
                        }
                   }// en foreach feed
                }// end for  each product
            } // end loop json
        }
        return true;
    }

      /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
        ];
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
                    $alertsMencionsModel = $this->_findAlertsMencions($product,$posts[$p]['id']);
                    $permalink = $posts[$p]['permalink'];
                    
                    if(!is_null($alertsMencionsModel) && !empty($posts[$p]['comments'])){
                       for($c = 0; $c <  sizeof($posts[$p]['comments']); $c++){
                           //var_dump($posts[$p]['comments'][$c]);
                            $user = $this->saveUserMencions($posts[$p]['comments'][$c]['username']);
                            if(empty($user->errors)){
                                $posts[$p]['comments'][$c]['permalink'] = $permalink;
                                $mention = $this->saveMencions($posts[$p]['comments'][$c],$alertsMencionsModel->id,$user->id);
                                if($this->isDictionaries && ArrayHelper::keyExists('wordsId', $posts[$p]['comments'][$c], false)){
                                    $wordIds = $posts[$p]['comments'][$c]['wordsId'];
                                    // save Keywords Mentions 
                                    $this->saveKeywordsMentions($wordIds,$mention->id);
                                }
                                if(ArrayHelper::keyExists('replies', $posts[$p]['comments'][$c], false)){
                                    if(count($data[$product][$p]['comments'][$c]['replies']['data'])){
                                        $replies = $data[$product][$p]['comments'][$c]['replies']['data'];
                                        for($r = 0; $r < sizeof($replies); $r++ ){
                                            if(isset($replies[$r]['message_markup'])){
                                                $user_replies = $this->saveUserMencions($replies[$r]['username']);
                                                $posts[$p]['comments'][$c]['permalink'] = $permalink;
                                                $replies_mention = $this->saveMencions($replies[$r],$alertsMencionsModel['id'],$user_replies->id);
                                                if($this->isDictionaries && ArrayHelper::keyExists('wordsId', $replies[$r], false)){
                                                    $wordIds = $replies[$r]['wordsId'];
                                                    // save Keywords Mentions 
                                                    $this->saveKeywordsMentions($wordIds,$replies_mention->id);
                                                }

                                            }
                                        }
                                    }

                                }
                            }else{
                                $error['user'][] = $user->errors;
                            }
                       }// end loop comments
                    }// end if is_null alertsMencionsModel
                }
            }// end foreach data
        } // end if null
        return (empty($error)) ? true : false;
    }

     /**
     * [searchDataByDictionary search keywords in the feed]
     * @param  [array] $mentions 
     * @return [array] [$mentions]
     */
    private function searchDataByDictionary($mentions){
        $words = \app\models\Keywords::find()->where(['alertId' => $this->alertId])->select(['name','id'])->asArray()->all();


        foreach($mentions as $product => $feeds){
            for($f = 0; $f < sizeof($feeds); $f++){
                if(ArrayHelper::keyExists('comments', $feeds[$f], false) && !empty($feeds[$f]['comments'])){
                    // var_dump($feeds[$f]['comments']);
                    // var_dump($mentions[$product][$f]['comments']);
                    for($c = 0; $c <  sizeof($feeds[$f]['comments']); $c++){
                        $wordsId = [];
                        for($w = 0; $w < sizeof($words); $w++){
                            $sentence = $mentions[$product][$f]['comments'][$c]['message_markup'];
                            $word = " {$words[$w]['name']} ";
                            $containsCount = \app\helpers\StringHelper::containsCount($sentence, $word);
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
                                            $sentence_replies = $mentions[$product][$f]['comments'][$c]['replies']['data'][$r]['message_markup'];
                                            $containsCount = \app\helpers\StringHelper::containsCount($sentence_replies, $word);
                                            if($containsCount){
                                                $wordsIdReplies[$words[$w]['id']] = $containsCount;
                                                $word_replies = $words[$w]['name'];
                                                $mentions[$product][$f]['comments'][$c]['replies']['data'][$r]['message_markup']  = \app\helpers\StringHelper::replaceIncaseSensitive($sentence_replies,$word_replies,"<strong>{$word_replies}</strong>");
                                            }

                                        }
                                    }// end loop words
                                    if(!empty($wordsIdReplies)){
                                        $mentions[$product][$f]['comments'][$c]['replies']['data'][$r]['wordsId'] = $wordsIdReplies;
                                    }
                                    else{
                                        unset($mentions[$product][$f]['comments'][$c]['replies']['data'][$r]);
                                        // restore key
                                        $mentions[$product][$f]['comments'][$c]['replies']['data'] = array_values($mentions[$product][$f]['comments'][$c]['replies']['data']);
                                    }
                                } // end loop replies
                            } // end if count replies data
                        } // end if replies
                        if(!empty($wordsId)){
                            $mentions[$product][$f]['comments'][$c]['wordsId'] = $wordsId;
                        }
                        // fix search
                        // else{
                        //     unset($mentions[$product][$f]['comments'][$c]);
                        //     unset($feeds[$f]['comments'][$c]);
                        //     // restore key
                        //     $mentions[$product][$f]['comments'] = array_values($mentions[$product][$f]['comments']);
                        //     $feeds[$f]['comments'] = array_values($feeds[$f]['comments']);
                        //     var_dump($mentions[$product][$f]['comments']);
                        //     var_dump($feeds[$f]['comments']);
                        //     die();
                        // }
                    }// end loop comments
                }// end if keyExists && !empty
            }// end loop feeds
        }// for each
        // print_r($mentions);
        // die();
        return $mentions;
    }

    /**
     * [_isDictionaries is the alert hace dictionaries]
     * @return boolean [description]
     */
    private function _isDictionaries(){
        if(!is_null($this->alertId)){
            $keywords = \app\models\Keywords::find()->where(['alertId' => $this->alertId])->exists();
            return $keywords;
        }
        return false;
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
     * [_setResourceId return the id from resource]
     */
    private function _setResourceId(){
        
        $socialId = (new \yii\db\Query())
            ->select('id')
            ->from('type_resources')
            ->where(['name' => 'Social media'])
            ->one();
        
        
        $resourcesId = (new \yii\db\Query())
            ->select('id')
            ->from('resources')
            ->where(['name' => 'Instagram Comments','resourcesId' => $socialId['id']])
            ->all();
        

        return ArrayHelper::getColumn($resourcesId,'id')[0];

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
            return \yii\helpers\Json::encode($response->data);
        }
        return null;
    }


}
