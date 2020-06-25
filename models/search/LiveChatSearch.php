<?php

namespace app\models\search;

use yii\helpers\ArrayHelper;

/**
 * LiveChatSearch represents the model behind the search form of `app\models\api\LiveChatsApi`.
 *
 */
class LiveChatSearch {

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


      
        for($p = 1 ; $p < sizeof($params); $p++){
            foreach($params[$p] as $data => $group){
                foreach($group as $pages => $products){
                    foreach($products as $product => $chats){
                        //echo $product."\n";
                        if(!ArrayHelper::keyExists($product,$this->data)){
                            $this->data[$product] = [];
                        }// end if keyExists
                        for($t = 0 ; $t < sizeOf($chats); $t++){
                            if(!in_array($chats[$t], $this->data[$product])){
                                $this->data[$product][] = $chats[$t];
                            }// end if in_array
                        }// end loop tickets
                    }
                }// end foreach products
            }// end foreach group
        }// end loop
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
            // echo "save data .. \n";
            // save all data
            $chats = $this->data;
            $search = $this->saveChats($chats);
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
           // echo "only dictionaries \n";
            $data = $this->data;
            $filter_data = $this->searchDataByDictionary($data);
            $search = $this->saveChats($filter_data);
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
     * [saveChats save chat in db]
     * @return [type] [description]
     */
    private function saveChats($data){

    	$error = [];

    	foreach ($data as $product => $chats){
    		$alertsMencionsModel = $this->findAlertsMencions($product);
            if(!is_null($alertsMencionsModel)){
                for($c = 0 ; $c < sizeof($chats); $c ++){

                    $chatId = $chats[$c]['id']; 
                    $chat_start_url = $chats[$c]['chat_start_url']; 
                    $visitor = $this->saveUserMentions($chats[$c]['visitor']);
                    $agent =  $this->saveUserMentions($chats[$c]['agents']);

                    if(ArrayHelper::keyExists('messages', $chats[$c])){
                        for($m = 0; $m < sizeOf($chats[$c]['messages']); $m++){
                            if(!\app\helpers\StringHelper::isEmpty($chats[$c]['messages'][$m]['text'])){
                                $author = ($chats[$c]['messages'][$m]['user_type'] == 'visitor') ? $visitor : $agent;
                                $chats[$c]['messages'][$m]['chat_start_url'] = $chat_start_url;
                                $mention = $this->saveMentions($chats[$c]['messages'][$m],$alertsMencionsModel->id,$author);
                                if(empty($mention->errors)){
                                    if(ArrayHelper::keyExists('wordsId', $chats[$c]['messages'][$m])){
                                        $wordsId = $chats[$c]['messages'][$m]['wordsId'];
                                        $this->saveKeywordsMentions($wordsId,$mention->id);
                                    }
                                }else{$error['mention'] = $mention->errors; }// end if errors
                            } // end if isEmpty
                        }// end loop messages
                    }// end fi keyExists messages
                }// end for chats
            }
    	}// end foreach data
    
        return (empty($error)) ? true : false;
    }

    /**
     * [searchDataByDictionary search words on each sentence ]
     * @param  [type] $chats [description]
     * @return [type]        [description]
     */
    private function searchDataByDictionary($data){
    	$words = \app\models\Keywords::find()->where(['alertId' => $this->alertId])->select(['name','id'])->asArray()->all();


    	foreach($data as $product => $chats){
    		for($c = 0; $c < sizeOf($chats); $c ++){
    			if(ArrayHelper::keyExists('messages', $chats[$c], false)){
    				for($m = 0; $m < sizeOf($chats[$c]['messages']); $m ++){
    					$wordsId = [];
    					for($w = 0; $w < sizeof($words); $w++){
    						$sentence = $data[$product][$c]['messages'][$m]['message_markup'];
                            $word = " {$words[$w]['name']} ";
    						$containsCount = \app\helpers\StringHelper::containsCount($sentence, $word);
    						if($containsCount){
    							$wordsId[$words[$w]['id']] = $containsCount;
    							$data[$product][$c]['messages'][$m]['message_markup']  = \app\helpers\StringHelper::replaceIncaseSensitive($sentence,$word,"<strong>{$word}</strong>");

    						}// end if containsCount
    					}// end loop words
    					if(!empty($wordsId)){
                            $data[$product][$c]['messages'][$m]['wordsId'] = $wordsId;
                        }// end if wordsId
    				}// end loop messages
    			} // end if keyExists
    		}// en looop chats 
        }// end foreach

        return $data;
    }

    /**
     * [saveUserMentions save the user if is not in db or return is found it]
     * @param  [array] $user [description]
     * @return [obj]       [user instance]
     */
    private function saveUserMentions($user){
        $user_data = [];
        if(ArrayHelper::keyExists('name', $user)){
            $screen_name = $user['id'];
            $name = $user['name'];
            $user_data['geo'] = [
                'city'    => $user['city'],
                'mobile'  => (!\app\helpers\StringHelper::containsCount($user['user_agent'],'Windows')) ? true : false,
                'country' => $user['country'],
                'region'  => $user['region'],
            ];
            $user_data['type'] = 'client';
            
        }else{
         
            $name = $user[0]['display_name'];
            $screen_name = $user[0]['email'];
            $user_data['type'] = 'agent';
            
        }// end if keyExists

        $where = [
                'screen_name' => $screen_name
        ];

        $isUserExists = \app\models\UsersMentions::find()->where($where)->exists();

        if($isUserExists){
            $model = \app\models\UsersMentions::find()->where($where)->one();
        }else{
            $model = new \app\models\UsersMentions();
            $model->name = $name;
            $model->screen_name = $screen_name;
            $model->user_data = (!empty($user_data)) ? $user_data : null;
            $model->save(); 
        }

        return $model;
    }
    /**
     * [saveMentions save mentions in db]
     * @param  [type] $chat             [description]
     * @param  [type] $alertsMencionsId [description]
     * @param  [type] $user             [description]
     * @return [type]                   [description]
     */
    private function saveMentions($chat,$alertsMencionsId,$user){

        $name      = $chat['author_name'];
        $message   = $chat['text'];
        $timestamp = $chat['timestamp'];
        
        $ticketId = explode('_', $chat['event_id']);
        $mention_data['event_id'] = $ticketId[0];
        // keep track by social id
        $social_id = hexdec( substr(sha1($mention_data['event_id']), 0, 15) );


        $message_markup = $chat['message_markup'];
        $url            = ($user->user_data['type'] == 'client') ? $chat['chat_start_url'] : null;
        $domain_url     = ($user->user_data['type'] == 'client') ? \app\helpers\StringHelper::getDomain($chat['chat_start_url']) : null;

        $where = [
            'created_time'    => $timestamp,
            'message'         => $message,
            'origin_id'       => $user->id,
            'alert_mentionId' => $alertsMencionsId,
        ];

        $isMentions = \app\models\Mentions::find()->where($where)->exists();

        if($isMentions){
            $model = \app\models\Mentions::find()->where($where)->one();

        }else{
            
            $model = new \app\models\Mentions();
            
            $model->alert_mentionId = $alertsMencionsId;
            $model->origin_id       = $user->id;
            $model->created_time    = $timestamp;
            $model->mention_data    = $mention_data;
            $model->message         = $message;
            $model->message_markup  = $message_markup;
            $model->url             = $url;
            $model->domain_url      = $domain_url;
            $model->social_id       = $social_id;
            
            $model->save();
        }
        return $model;

    }

     /**
     * [saveKeywordsMentions save keywords id on table pivote]
     * @param  [array] $wordsId   [id words and each repate in the sentence]
     * @param  [type] $mentionId [id of the mentionId]
     * @return [type]            [description]
     */
    private function saveKeywordsMentions($wordsId,$mentionId){

        foreach($wordsId as $idwords => $count){
            if(!\app\models\KeywordsMentions::find()->where(['mentionId'=> $mentionId,'keywordId' => $idwords])->exists()){
                for($c = 0; $c < $count; $c++){
                    $model = new \app\models\KeywordsMentions();
                    $model->keywordId = $idwords;
                    $model->mentionId = $mentionId;
                    $model->save();
                }
            }
            
        }

    }

    /**
     * Finds the AlertsMencions model based on product key value.
     * @param string $product
     * @return AlertsMencions the loaded model
     */
    private function findAlertsMencions($product)
    {

        $alertsMencions =  \app\models\AlertsMencions::find()->where([
            'alertId'       => $this->alertId,
            'resourcesId'   =>  $this->resourcesId,
            //'condition'     =>  'ACTIVE',
            'type'          =>  'chat',
            'term_searched' =>  $product,
        ])
        ->select('id')->one();
       
        return $alertsMencions;

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
            ->where(['name' => 'Live Chat Conversations','resourcesId' => $socialId['id']])
            ->all();
        

        return ArrayHelper::getColumn($resourcesId,'id')[0];

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

}


