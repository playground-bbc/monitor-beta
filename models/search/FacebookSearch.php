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
                   // for each feeds 
                   for($d = 0; $d < sizeof($datos); $d++){
                        if(!ArrayHelper::keyExists($product, $this->data, false)){
                            $this->data[$product] = [];
                        }
                        if(!in_array($datos[$d], $this->data[$product])){
                            $this->data[$product] [] = $datos[$d];
                        }
                   }// en foreach feeds
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
          //  echo "only dictionaries \n";
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
                        $origin = $this->savePostMencions($posts[$p]);
                        if(!$origin->errors){
                            if(!empty($posts[$p]['comments'])){
                                $comments = $posts[$p]['comments'];
                                foreach($comments as $index => $comment){
                                    if(!\app\helpers\StringHelper::isEmpty($comment['message'])){
                                        $mention = $this->saveComments($comment,$alertsMencionsModel->id,$origin->id);
                                    
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
                    }
                }
            }
        }

        return (empty($error)) ? true : false;
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


    private function searchDataByDictionary($feeds){
        $words = \app\models\Keywords::find()->where(['alertId' => $this->alertId])->select(['name','id'])->asArray()->all();
        

        foreach($feeds as $product => $posts){
            for($p = 0; $p < sizeof($posts); $p++){
                if(ArrayHelper::keyExists('comments', $posts[$p], false) && !empty($posts[$p]['comments'])){
                    for($c=0; $c < sizeof($posts[$p]['comments']); $c++){
                        $wordsId = [];
                        for($w = 0; $w < sizeof($words); $w++){
                            $sentence = $feeds[$product][$p]['comments'][$c]['message_markup'];
                            $word = " {$words[$w]['name']} ";
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
            'resourcesId'    =>  5,
            //'condition'      =>  'ACTIVE',
            'type'           =>  'comments',
            'term_searched'  =>  $product,
            'publication_id' =>  $publication_id,
        ])
        ->select('id')->one();

        return $alertsMencions;

    } 


    public function savePostMencions($post){
        // $user_data['is_popular'] = $post['is_popular'];
        // $user_data['shares'] = $post['shares'];

        $id = explode("_",$post['id']);
        $id = end($id);


        $author = \app\helpers\StringHelper::remove_emoji($post['from']);
        $message = $post['message'];
        $short_message = \app\helpers\StringHelper::ensureRightPoints(\app\helpers\StringHelper::substring($message,0,385));
        $origin = \app\helpers\MentionsHelper::saveUserMencions(
            [
                'user_uuid' => $id
            ],
            [
                'name'        => $author,
                'screen_name' => $author,
              //  'user_data'   => $user_data,
                'message'     => \app\helpers\StringHelper::remove_emoji($short_message),
            ]
        );



        return $origin;
    }

    public function saveComments($comment,$alertId,$originId){

        $created_time = \app\helpers\DateHelper::asTimestamp($comment['created_time']);

        $mention_data['like_count'] = (isset($comment['like_count'])) ? $comment['like_count']: 0;
        $message = $comment['message'];
        $id = explode("_",$comment['id']);
        $id = end($id);
        $message_markup = $comment['message_markup'];
        $url = (isset($comment['permalink_url'])) ? $comment['permalink_url'] : '-';

        
        $mention = \app\helpers\MentionsHelper::saveMencions(
            [
                'alert_mentionId' => $alertId,
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
             die();
        }

        return $mention;
        
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
