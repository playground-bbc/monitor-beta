<?php

namespace app\models\search;

use yii\helpers\ArrayHelper;
use yii\db\Command;
/**
 * FacebookMessagesSearch represents the model behind the search form of `app\models\api\FacebookMessagesApi`.
 */

class FacebookMessagesSearch {

	public $alertId;
    public $resourcesId;
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
        // is isDictionaries
        $this->isDictionaries = $this->_isDictionaries();
        // set resourcesId
        $this->resourcesId    = $this->_setResourceId();

        // loop data
        for($p = 1; $p < sizeof($params); $p++){
            // loop with json file
            for($j = 0; $j < sizeof($params[$p]); $j++){
                $model = $params[$p][$j][0];
                foreach ($model as $product => $comments_ids){
                    
                    if(!ArrayHelper::keyExists($product, $this->data, false)){
                        $this->data[$product] = [];
                    }// end if keyExists

                    // for each comments_ids 
                    foreach($comments_ids as $comment_id => $comments){

                        if(!ArrayHelper::keyExists($comment_id, $this->data[$product], false)){
                            $this->data[$product][$comment_id][] = $comments;
                        }

                    } // end foreach comments_ids
                }// end foreach model
                
            } // end loop json
        }

        return true;
    }
    


     /**
     * {@inheritdoc}
     */
    public function rules()
    {
        
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
            // echo "no dictionaries .. \n";
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
            $model = $this->data;
            $data = $this->searchDataByDictionary($model);
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


    private function saveMentions($model){
        $error = [];
        if(!is_null($model)){
            foreach($model as $product => $ids_messages){
                foreach ($ids_messages as $id_message => $data){
                    $alertsMencionsModel = $this->_findAlertsMencions($product,$id_message);
                    foreach ($data as $index => $messages){
                        if(!is_null($alertsMencionsModel) && count($messages)){
                            for($m = 0; $m < sizeof($messages); $m++){
                                if(!empty($messages[$m]['message'])){
                                    $user = $this->saveUserMencions($messages[$m]['from']);
                                    if($user->errors){
                                        $error['user'][] = $user->errors;
                                        //break;
                                    }
                                    $mention = $this->saveMessage($messages[$m],$alertsMencionsModel,$user->id);
                                    
                                    if($mention->errors){
                                        $error['mention'][] = ['error' => $mention->errors,'alerts:mention_id' => $alertsMencionsModel->id,'userId' => $user->id ,'messages' => $messages[$m]['message']];
                                        //break;
                                    }
                                    else{
                                        if($user->name != 'Mundo LG' && strlen($mention->message) > 2){
                                            $this->saveOrUpdatedCommonWords($mention,$alertsMencionsModel->id); 
                                        }
                                    }
                                    if(ArrayHelper::keyExists('wordsId', $messages[$m], false)){
                                        $wordIds = $messages[$m]['wordsId'];
                                        // save Keywords Mentions 
                                        $this->saveKeywordsMentions($wordIds,$mention->id);
                                    }else{
                                       // in case update in alert
                                        if(\app\models\KeywordsMentions::find()->where(['mentionId' => $mention->id])->exists()){
                                            \app\models\KeywordsMentions::deleteAll('mentionId = '.$mention->id);
                                        }
                                    }
                                }

                            }

                        }
                    }
                }
            }
        }
        
       return (empty($error)) ? true : false;
    }

    private function searchDataByDictionary($model){

        $words = \app\models\Keywords::find()->where(['alertId' => $this->alertId])->select(['name','id'])->asArray()->all();

        foreach($model as $product => $ids_messages){
           // echo $product."\n";
            foreach ($ids_messages as $id_message => $data){
                $inboxs = \yii\helpers\ArrayHelper::remove($model[$product][$id_message], 0);
                
                for ($i=0; $i < sizeOf($inboxs) ; $i++) { 
                    if(\yii\helpers\ArrayHelper::keyExists('message_markup', $inboxs[$i])){
                        $wordsId = [];
                        $sentence = \app\helpers\StringHelper::lowercase($inboxs[$i]['message_markup']);
                        for($w = 0; $w < sizeof($words); $w++){
                            $word = \app\helpers\StringHelper::lowercase($words[$w]['name']);
                            $containsCount = \app\helpers\StringHelper::containsCount($sentence, $word);
                            if($containsCount){
                                $wordsId[$words[$w]['id']] = $containsCount;
                                $inboxs[$i]['message_markup']  = \app\helpers\StringHelper::replaceIncaseSensitive($sentence,$word,"<strong>{$word}</strong>");
                            } // end if containsCount
                        }// end loop words
                        if(count($wordsId)){
                            $inboxs[$i]['wordsId'] = $wordsId;
                            $model[$product][$id_message][0][] = $inboxs[$i];
                            
                        }
                        unset($sentence);
                        unset($wordsId);
                    }// end if keyexists
                }// end loop inbox
            } // end foreach ids_messages           
        }// end foreach model
        return $model;
    }



    private function saveUserMencions($user){

        $user_data['email'] = $user['email'];


        $user = \app\helpers\MentionsHelper::saveUserMencions(
            [
                'user_uuid' => $user['id']
            ],
            [
                'name'        => $user['name'],
                'screen_name' => $user['name'],
                'user_data'   => $user_data,
                'message'     => '',
            ]
        );

        return $user;
    }


    private function saveMessage($messages,$alertsMencionsModel,$originId){

        $created_time = \app\helpers\DateHelper::asTimestamp($messages['created_time']);

        $url = (!empty($messages['url']))  ? "https://www.facebook.com".$messages['url'] : '-';
        $message = $messages['message'];
        $message_markup = $messages['message_markup'];
        // keep track by social id
        $social_id = hexdec( substr(sha1($alertsMencionsModel->publication_id), 0, 15) );

        
        $mention = \app\helpers\MentionsHelper::saveMencions(
            [
                'alert_mentionId' => $alertsMencionsModel->id,
                'origin_id'       => $originId,
                'created_time'    => $created_time,
            ],
            [
                'origin_id'      => $originId, // url is unique
                'social_id'      => $social_id,   
                'created_time'   => $created_time,
                'message'        => $message,
                'message_markup' => $message_markup,
                'url'            => $url,
                'domain_url'     => $url,
            ]
        );

        return $mention;
        
    }

    public function saveOrUpdatedCommonWords($mention,$alertsMencionId){
        // most repeated words
        $words = \app\helpers\ScrapingHelper::sendTextAnilysis($mention->message,$link = null);
       
        foreach($words as $word => $weight){
            if(!is_numeric($word)){
                $is_words_exists = \app\models\AlertsMencionsWords::find()->where(
                    [
                        'alert_mentionId' => $alertsMencionId,
                        'name' => $word,
                    ]
                )->exists();
                if (!$is_words_exists) {
                    $model = new \app\models\AlertsMencionsWords();
                    $model->alert_mentionId = $alertsMencionId;
                    $model->mention_socialId = $mention->social_id;
                    $model->name = $word;
                    $model->weight = $weight; 
                } else {
                    
                    $model = \app\models\AlertsMencionsWords::find()->where(
                        [
                            'alert_mentionId' => $alertsMencionId,
                            'name' => $word  
                        ])->one();
                    
                    $model->weight = $model->weight + $weight; 
                }
                if($model->validate()){
                    $model->save();
                }
            }
            
        }
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
     * Finds the AlertsMencions model based on product key value.
     * @param string $product
     * @return AlertsMencions the loaded model
     */
    private function _findAlertsMencions($product,$publication_id)
    {

        $alertsMencions =  \app\models\AlertsMencions::find()->where([
            'alertId'        => $this->alertId,
            'resourcesId'    =>  $this->resourcesId,
            //'condition'      =>  'ACTIVE',
            'type'           =>  'messages Facebook',
            'term_searched'  =>  $product,
            'publication_id' =>  $publication_id,
        ])
        ->select(['id','publication_id'])->one();

        return $alertsMencions;

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
            ->where(['name' => 'Facebook Messages','resourcesId' => $socialId['id']])
            ->all();
        

        return ArrayHelper::getColumn($resourcesId,'id')[0];

    }

}