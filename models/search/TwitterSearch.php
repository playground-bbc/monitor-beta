<?php

namespace app\models\search;

use yii\helpers\ArrayHelper;

/**
 * TwitterSearch represents the model behind the search form of `app\models\api\TwitterApi`.
 */
class TwitterSearch 
{
    public $alertId;
    public $data = [];
    public $isDictionaries = false;
    public $isBoolean = false;

    /**
     * [load load in to local variables]
     * @param  [array] $params [product [tweets]]
     * @return [boolean]
     */
    public function load($params){
        if(empty($params)){
           return false;     
        }
        $this->alertId = ArrayHelper::getValue($params, 0);
        $this->isDictionaries = $this->_isDictionaries();
        // is boolean
        
        // loop data
        for($p = 1; $p < sizeof($params); $p++){
            // loop with json file
            for($j = 0; $j < sizeof($params[$p]); $j++){
                $products = $params[$p][$j][0];
                // for each product
                foreach($products as $product => $datos){
                   // for each tweets 
                   for($d = 0; $d < sizeof($datos); $d++){
                        if(!ArrayHelper::keyExists($product, $this->data, false)){
                            $this->data[$product] = [];
                        }
                        if(!in_array($datos[$d], $this->data[$product])){
                            $this->data[$product] [] = $datos[$d];
                        }
                   }// en foreach tweets
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
             //echo "save data .. \n";
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
            //echo "only dictionaries \n";
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
     * [saveMentions save  mentions or update]
     * @param  [array] $data [array]
     * @return [boolean]
     */
    private function saveMentions($data){
        $error = [];
        if(!is_null($data)){
            foreach($data as $product => $tweets){
                $alertsMencions =  $this->findAlertsMencionsByProducts($product);
                if(!is_null($alertsMencions)){
                    // loop over tweets
                    for($t = 0; $t < sizeof($tweets); $t++){
                        if(!\app\helpers\StringHelper::isEmpty($tweets[$t]['message'])){
                            // save user mentions                    
                            $origin = $this->saveUserMencions($tweets[$t]['user']);

                            if(!$origin->errors){
                                // save mentions 
                                $mention = $this->saveMencions($tweets[$t],$alertsMencions->id,$origin->id);

                                if(empty($mention->errors)){
                                    // if words find it
                                    if(ArrayHelper::keyExists('wordsId', $tweets[$t], false)){
                                        $wordIds = $tweets[$t]['wordsId'];
                                        // save Keywords Mentions 
                                        $this->saveKeywordsMentions($wordIds,$mention->id);
                                        
                                    }
                                    else{
                                        // in case update in alert
                                        if(\app\models\KeywordsMentions::find()->where(['mentionId' => $mention->id])->exists()){
                                            \app\models\KeywordsMentions::deleteAll('mentionId = '.$mention->id);
                                        }
                                            
                                    }

                                }else{ 
                                    $error['mentions'] = $mention->errors;
                                    $origin->delete();
                                }

                            }else{ 
                                $error['oringin'] = $origin->errors;
                                
                            }
                        }
                    }
                }
            }
        }
        //var_dump($error);
        return (empty($error)) ? true : false;
    }

    /**
     * [searchDataByDictionary search keywords in the tweets]
     * @param  [array] $mentions 
     * @return [array] [$mentions]
     */
    private function searchDataByDictionary($mentions){
        $words = \app\models\Keywords::find()->where(['alertId' => $this->alertId])->select(['name','id'])->asArray()->all();

        $data = [];

        foreach($mentions as $product => $tweets){
           for($t = 0; $t < sizeof($tweets); $t++){
                //$msg = \app\helpers\StringHelper::remove_emoji($tweets[$t]['message']);
                $wordsId = [];
                for($w = 0; $w < sizeof($words); $w++){
                    $word = " {$words[$w]['name']} ";
                    $containsCount = \app\helpers\StringHelper::containsCount($tweets[$t]['message_markup'], $word);
                    if($containsCount){
                        $tweets[$t]['message_markup'] = \app\helpers\StringHelper::replace($tweets[$t]['message_markup'],$word,"<strong>{$word}</strong>");
                        $wordsId[$words[$w]['id']] = $containsCount;

                        
                    }
                }
                if(!empty($wordsId)){
                    if(!ArrayHelper::keyExists($product, $data, false)){
                    $data[$product] = [];
                    }
                    if(!in_array($tweets[$t], $data[$product])){
                        $tweets[$t]['wordsId'] = $wordsId;
                        $data[$product][] =  $tweets[$t];
                    }
                }
           } 
        }
        return $data;
    }



    /**
     * Finds the AlertsMencions model based on product key value.
     * @param string $product
     * @return AlertsMencions the loaded model
     */
    private function findAlertsMencionsByProducts($product)
    {

        $alertsMencions =  \app\models\AlertsMencions::find()->where([
            'alertId'       => $this->alertId,
            'resourcesId'   =>  1,
          //  'condition'    =>  'ACTIVE',
            'type'          =>  'tweet',
            'term_searched' =>  $product,
        ])->select('id')->one();


        return $alertsMencions;

    } 

    /**
     * [saveUserMencions save or update user mentions]
     * @param  [array] $user [ user data tweet]
     * @return [obj]   $origin      [model user]
     */
    private function saveUserMencions($user){

        $user_data['url'] = $user['url'];
        $user_data['location'] = $user['location'];
        $profile_image_url = $user['profile_image_url'];
        
        $user_data['followers_count'] = $user['followers_count'];
        $user_data['friends_count'] = $user['friends_count'];
        
        $author = (!\app\helpers\StringHelper::isEmpty($user['author_name'])) ? $user['author_name']: $user['author_username'] ;
        $origin = \app\helpers\MentionsHelper::saveUserMencions(
            [
                'user_uuid' => $user['user_id']
            ],
            [
                'name'        => $author,
                'screen_name' => $user['author_username'],
                'user_data'   => $user_data,
                'profile_image_url' => $profile_image_url
            ]
        );

        return $origin;

    }

    /**
     * [saveMencions save or update mentions]
     * @param  [array] $tweets           [tweet]
     * @param  [int] $alertsMencionsId   [alert mentions]
     * @param  [int] $originId           [id user ]
     * @return [obj]                     [model mentions]
     */
    private function saveMencions($tweets,$alertsMencionsId,$originId){

        $url          = (!empty($tweets['url'])) ? $tweets['url']['url'] : '-';
        $social_id    = $tweets['id'];
        $created_time = \app\helpers\DateHelper::asTimestamp($tweets['created_at']);
        $message      = $tweets['message'];
        $location     = \app\helpers\StringHelper::remove_emoji($tweets['user']['location']);
        $message_markup = $tweets['message_markup'];

        $mention_data['retweet_count'] = $tweets['retweet_count'];
        $mention_data['favorite_count'] = $tweets['favorite_count'];

        if(!\app\models\Mentions::find()->where(['alert_mentionId' => $alertsMencionsId,'origin_id' => $originId,'social_id' => $social_id])->exists()){
            $model                  = new \app\models\Mentions();
            $model->location        = '';
            $model->subject         = '';
            $model->url             = $url;
            $model->domain_url      = $url;
            $model->origin_id       = $originId;
            $model->message         = $message;
            $model->social_id       = $social_id;
            $model->mention_data    = $mention_data;
            $model->created_time    = $created_time;
            $model->message_markup  = $message_markup;
            $model->alert_mentionId = $alertsMencionsId;
            if(!$model->save())
                var_dump($model->errors);

        }else{
            $model = \app\models\Mentions::find()->where(['alert_mentionId' => $alertsMencionsId,'origin_id' => $originId,'social_id' => $social_id])->one();
        }

        /*$mention = \app\helpers\MentionsHelper::saveMencions(
            [
                'alert_mentionId' => $alertsMencionsId,
                'origin_id'       => $originId
            ],
            [
                'created_time'   => $created_time,
                'mention_data'   => $mention_data,
                'subject'        => '',
                'message'        => $message,
                'message_markup' => $message_markup,
                'url'            => $url ,
                'domain_url'     => $url ,
                'location'       => '-' ,
                'social_id'      => $social_id ,
            ]
        );*/
        
       // return (isset($model->id)) ? $model : $model->errors;
        return $model;

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


}
