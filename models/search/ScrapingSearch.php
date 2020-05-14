<?php

namespace app\models\search;

use yii\helpers\ArrayHelper;


/**
 * ScrapingSearch represents the model behind the search form of `app\models\api\Scraping`.
 */
class ScrapingSearch
{

  
 	public $alertId;
  	public $resourcesId;
  	public $data = [];
  	public $isDictionaries = false;
  	public $isBoolean = false;
  	public $resourceName = 'Paginas Webs';

  	public function load($params)
  	{
  		if (empty($params)) {
  			return false;
  		}

  		$this->alertId        = ArrayHelper::getValue($params, 0);
	    $this->resourcesId    = \app\helpers\AlertMentionsHelper::getResourceIdByName($this->resourceName);
	    $this->isDictionaries = \app\helpers\AlertMentionsHelper::isAlertHaveDictionaries($this->alertId);

	    for($p = 1 ; $p < sizeof($params); $p++){
	        foreach($params[$p] as $data => $group){
	            foreach($group as $pages => $terms){
	                foreach($terms as $term => $content){
	                    //echo $term."\n";
	                    if(!ArrayHelper::keyExists($term,$this->data)){
	                        $this->data[$term] = [];
	                    }// end if keyExists
	                    for($n = 0 ; $n < sizeOf($content); $n++){
	                        if(!in_array($content[$n], $this->data[$term])){
	                            $this->data[$term][] = $content[$n];
	                        }// end if in_array
	                    }// end loop news
	                }
	            }// end foreach terms
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
		     //echo "save data .. \n";
		    // save all data
		    $webContent = $this->data;
		    $search = $this->saveWebContent($webContent);
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
		    $webContent = $this->data;
		    $filter_data = $this->searchDataByDictionary($webContent);
		    $search = $this->saveWebContent($filter_data);
		    return $search;
		    
		}

		// if  !dictionaries and  boolean
		if(!$this->isDictionaries && $this->isBoolean){
		    // init search
		    echo "only boolean \n";
		    // retur something
		}

	}

	private function saveWebContent($webContent)
	{
		$error = [];
	    foreach ($webContent as $term => $content) {
	      $alertsMencionsModel = $this->findAlertsMencionsByterms($term);
	      if(!is_null($alertsMencionsModel)){
	        for ($c=0; $c < sizeof($content) ; $c++) { 
	        	$webPageName = $content[$c]['source']['name'];
	        	if (!empty($webPageName)) {
	        		$author = $this->saveUserMentions($webPageName);
	        		if (empty($author->errors)) {
			            $content_web = $content[$c];
			            $mention = $this->saveMencions($content_web,$alertsMencionsModel->id,$author->id);
			            if (empty($mention->errors)) {
			                if($this->isDictionaries && ArrayHelper::keyExists('wordsId', $content[$c], false)){
			                  $wordIds = $content[$c]['wordsId'];
			                  // save Keywords Mentions 
			                  $this->saveKeywordsMentions($wordIds,$mention->id);
			                }// end if isDictionaries
			            }else{
			                $author->delete();
			                $error['mention'] = $mention->errors;
			            }// end mention error
		            }else{
		              $error['user'] = $author->errors;
		            }// end error mention// end if author
	        	}// end if empty web page name
	        }// end loop content
	      }// end if !null
	    }// end foreach $webContent
	    return (empty($error)) ? true : false;
	}


	/**
	* Finds the AlertsMencions model based on terms key value.
	* @param string $product
	* @return AlertsMencions the loaded model
	*/
	private function findAlertsMencionsByterms($term)
	{
		$alertsMencions =  \app\models\AlertsMencions::find()->where([
			'alertId'       =>  $this->alertId,
			'resourcesId'   =>  $this->resourcesId,
			'type'          =>  \app\models\api\Scraping::TYPE_MENTIONS,
			'term_searched' =>  $term,
		])->select('id')->one();


		return $alertsMencions;
	}

	/**
	* [saveUserMentions save the user if is not in db or return is found it]
	* @param  [array] $user [description]
	* @return [obj]       [user instance]
	*/
	private function saveUserMentions($authorName){
		$authorName = $authorName;
		$where = [
		  'name' => $authorName,
		  'screen_name' => $authorName
		];

		$isUserExists = \app\models\UsersMentions::find()->where($where)->exists();

		if($isUserExists){
		    $model = \app\models\UsersMentions::find()->where($where)->one();
		}else{
		    $model = new \app\models\UsersMentions();
		    $model->name = $authorName;
		    $model->screen_name = $authorName;
		    $model->save(); 
		}

		return $model;
	}

	/**
	* [saveMencions save or update mentions]
	* @param  [array] $tweets           [tweet]
	* @param  [int] $alertsMencionsId   [alert mentions]
	* @param  [int] $originId           [id user ]
	* @return [obj]                     [model mentions]
	*/
	private function saveMencions($content,$alertsMencionsId,$originId){

		$created_time = \app\helpers\DateHelper::getToday();
		$message = $content['content'];
		$message_markup = $content['message_markup'];
		$url = $content['url'];
		$domain_url = \app\helpers\StringHelper::getDomain($content['url']);


		$mention = \app\helpers\MentionsHelper::saveMencions(
		    [
		        'alert_mentionId'     => $alertsMencionsId,
		        'origin_id'           => $originId ,
		        'message'             => $message,
		    ],
		    [
		        'created_time'   => $created_time,
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
	* [searchDataByDictionary looking in data words in the dictionaries]
	* @param  [array] $data [description]
	* @return [array]       [description]
	*/
	private function searchDataByDictionary($data){
		$words = \app\models\Keywords::find()->where(['alertId' => $this->alertId])->select(['name','id'])->asArray()->all();

		$model = [];  

		foreach($data as $term => $content){
			for($c = 0; $c < sizeOf($content); $c ++){
			  $wordsId = [];
			  for ($w=0; $w <sizeof($words) ; $w++) { 
			    $sentence = $data[$term][$c]['message_markup'];
			    $word = "{$words[$w]['name']}";
			    $containsCount = \app\helpers\StringHelper::containsCountIncaseSensitive($sentence, $word);
			    if ($containsCount) {
			      $wordsId[$words[$w]['id']] = $containsCount;
			      $data[$term][$c]['message_markup']  = \app\helpers\StringHelper::replaceIncaseSensitive($sentence,$word,"<strong>{$word}</strong>");
			    //  var_dump($data[$term][$c]['message_markup']);
			    }
			  }// end loop words
			  if(!empty($wordsId)){
			    if(!ArrayHelper::keyExists($term, $model)){
			          $model[$term] = [];
			      }
			      if(!in_array($data[$term][$c], $model[$term])){
			          $data[$term][$c]['wordsId'] = $wordsId;
			          $model[$term][] =  $data[$term][$c];
			      }
			      //$data[$term][$n]['wordsId'] = $wordsId;
			  }
			} // end loop news
		}// end foreach
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