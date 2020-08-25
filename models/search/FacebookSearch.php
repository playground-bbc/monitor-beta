<?php

namespace app\models\search;

use yii\helpers\ArrayHelper;
use yii\db\Command;
/**
 * FacebookSearch represents the model behind the search form of `app\models\api\FacebookCommentsApi`.
 */
class FacebookSearch 
{

    public $alertId;
    public $data = [];
    public $isDictionaries = false;
    public $isBoolean = false;

    /**
     * [load load in to local variables]
     * @param  [array] $params [product [feeds]]
     * @return [boolean]
     */
    public function load($data){
        if(empty($data)){
           return false;     
        }
        $this->resourceId = \app\helpers\AlertMentionsHelper::getResourceIdByName('Facebook Comments');
        $this->isDictionaries = \app\helpers\AlertMentionsHelper::isAlertHaveDictionaries($this->alertId);
        
        
        $this->data = current($data);
        unset($data);
        return (count($this->data)) ? true : false;
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
                    $alertsMencionsModel = $this->findAlertsMencions($product,$posts[$p]['id']);
                    if(!is_null($alertsMencionsModel)){
                        $transaction = \Yii::$app->db->beginTransaction();
                        try {
                            $origin = $this->savePostMencions($posts[$p]);
                            if(!$origin->errors){
                                if(!empty($posts[$p]['comments'])){
                                    $comments = $posts[$p]['comments'];
                                    foreach($comments as $index => $comment){
                                        if(!\app\helpers\StringHelper::isEmpty($comment['message'])){
                                            $mention = $this->saveComments($comment,$alertsMencionsModel->id,$origin->id);
                                            // save repeated words
                                            if(strlen($mention->message) > 2){
                                                $this->saveOrUpdatedCommonWords($mention,$alertsMencionsModel->id);
                                            }
                                            
                                            if(empty($mention->errors)){
                                                if(ArrayHelper::keyExists('wordsId', $comment, false)){
                                                    $wordIds = $comment['wordsId'];
                                                    // save Keywords Mentions 
                                                    $this->saveKeywordsMentions($wordIds,$mention->id);
                                                }else{
                                                // in case update in alert
                                                    if(\app\models\KeywordsMentions::find()->where(['mentionId' => $mention->id])->exists()){
                                                        \app\models\KeywordsMentions::deleteAll('mentionId = '.$mention->id);
                                                    }
                                                }
                                            }else{ 
                                                $error['mentions'] = $mention->errors;
                                                $origin->delete();
                                            }
                                        }else{ continue; }
                                    }
                                }
                            }else{ 
                                $error['origin'] = $origin->errors;
                            }
                            $transaction->commit();
                        } catch (\Exception $e) {
                            $transaction->rollBack();
                            throw $e;
                        }
                    }
                }
            }
        }

        return (empty($error)) ? true : false;
    }


    /**
     * [searchDataByDictionary search keywords in the feed]
     * @param  [array] $feeds 
     * @return [array] [$feeds]
     */
    private function searchDataByDictionary($feeds){
        $words = \app\helpers\AlertMentionsHelper::getDictionariesWords($this->alertId);
        foreach($feeds as $product => $posts){
            for($p = 0; $p < sizeof($posts); $p++){
                if(ArrayHelper::keyExists('comments', $posts[$p], false) && !empty($posts[$p]['comments'])){
                    for($c=0; $c < sizeof($posts[$p]['comments']); $c++){
                        $wordsId = [];
                        for($w = 0; $w < sizeof($words); $w++){
                            $sentence = \app\helpers\StringHelper::lowercase($feeds[$product][$p]['comments'][$c]['message_markup']);
                            $word = \app\helpers\StringHelper::lowercase($words[$w]['name']);
                            $containsCount = \app\helpers\StringHelper::containsCount($sentence, $word);


                            if($containsCount){
                                $wordsId[$words[$w]['id']] = $containsCount;
                                $feeds[$product][$p]['comments'][$c]['message_markup']  = \app\helpers\StringHelper::replaceIncaseSensitive($sentence,$word,"<strong>{$word}</strong>");
                            }// end if contains
                        } // end loop words
                        if(!empty($wordsId)){
                            $feeds[$product][$p]['comments'][$c]['wordsId'] = $wordsId;
                        }else{
                            unset($feeds[$product][$p]['comments'][$c]);
                        }
                    }// end loop comments
                } // if comments
            }// end loop posts
        }// for each feeds

        return $feeds;
    }

    /**
     * Finds the AlertsMencions model based on product key value.
     * @param string $product
     * @return AlertsMencions the loaded model
     */
    private function findAlertsMencions($product,$publication_id)
    {

        $alertsMencions =  \app\models\AlertsMencions::find()->where([
            'alertId'        => $this->alertId,
            'resourcesId'    =>  $this->resourceId,
            //'condition'      =>  'ACTIVE',
            'type'           =>  'comments',
            'term_searched'  =>  $product,
            'publication_id' =>  $publication_id,
        ])
        ->select(['id','publication_id'])->one();

        return $alertsMencions;

    } 

    /**
     *  savePostMencions save post mencions
     * @param array $post
     * @return origin the loaded model origin
     */
    public function savePostMencions($post){
        // $user_data['is_popular'] = $post['is_popular'];
        // $user_data['shares'] = $post['shares'];

        $id = explode("_",$post['id']);
        $id = end($id);


        //$author = \app\helpers\StringHelper::remove_emoji($post['from']);
        $author = 'User Facebook';
        //$message = $post['message'];
        //$short_message = \app\helpers\StringHelper::ensureRightPoints(\app\helpers\StringHelper::substring($message,0,385));
        $origin = \app\helpers\MentionsHelper::saveUserMencions(
            [
                'name' => $author
            ],
            [
                'user_uuid'   => $id,
                'name'        => $author,
                'screen_name' => $author,
              //  'user_data'   => $user_data,
               // 'message'     => \app\helpers\StringHelper::remove_emoji($short_message),
            ]
        );



        return $origin;
    }
    /**
     *  saveComments save comments
     * @param array $comment
     * @param int $alertsMencionId
     * @param int $originId
     * @return mention the loaded model mention
     */
    public function saveComments($comment,$alertsMencionId,$originId){

        $created_time = \app\helpers\DateHelper::asTimestamp($comment['created_time']);

        $mention_data['like_count'] = (isset($comment['like_count'])) ? $comment['like_count']: 0;
        $message = $comment['message'];
        $id = explode("_",$comment['id']);
        $id = end($id);
        $message_markup = $comment['message_markup'];
        $url = (isset($comment['permalink_url'])) ? $comment['permalink_url'] : '-';

        
        $mention = \app\helpers\MentionsHelper::saveMencions(
            [
                'alert_mentionId' => $alertsMencionId,
                'origin_id'       => $originId, // url is unique
                'social_id'       => $id,
            ],
            [
                'created_time'   => $created_time,
                'mention_data'   => $mention_data,
                'message'        => $message,
                'message_markup' => $message_markup,
                'url' => $url,
                'domain_url' => $url,
            ]
        );

        if($mention->errors){
            var_dump($mention->errors);
        }
        return $mention;
        
    }
    /**
     *  saveComments common words
     * @param array $mention
     * @param int $alertsMencionId
     */
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



}
