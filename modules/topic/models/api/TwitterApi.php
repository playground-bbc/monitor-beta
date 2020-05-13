<?php
namespace app\modules\topic\models\api;

use Yii;



/**
 * wrapper Twitter for topics
 */
class TwitterApi
{
	public $topicId;
	public $userId;
	public $end_date;
	public $resourceId;
	public $locations;
	public $codebird;
	public $remaining = 10;
	public $apistatus = true;
	public $data;


	public function prepare($topic)
	{
		$this->topicId = $topic['id'];
		$this->userId = $topic['userId'];
		$this->end_date = $topic['end_date'];
		$this->resourceId = \app\helpers\AlertMentionsHelper::getResourceIdByName($topic['resource']['name']);
		// get locations topics
		$this->locations = \app\helpers\TwitterHelper::getLocationsForTopicId($this->topicId);
		
		// get twitter login api
		$this->codebird = \app\helpers\TwitterHelper::login($this->resourceId);

		return ($this->codebird && $this->apistatus);
	}

	public function callTrendings()
	{
		$this->codebird->setReturnFormat(CODEBIRD_RETURNFORMAT_ARRAY);
		$this->codebird->setTimeout(4000);
		$this->codebird->setConnectionTimeout(9000);


		foreach ($this->locations as $locationId => $woeid) {
			$data[$locationId] = $this->codebird->trends_place(['id' => $woeid], true);
			if($data[$locationId]['rate']['remaining'] <= $this->remaining){
				echo "out limit ....\n";
				$this->apistatus = false;
        		break;
        	}

        	if ($data[$locationId]['httpstatus'] == 200) {
        		if (isset($data[$locationId][0]['trends'])) {
        			$this->data[$locationId] = $data[$locationId][0]['trends'];
        		}
        	}
		}
		return $this->data;

	}

	public function saveData()
	{
		$data = [];

		foreach ($this->data as $locationId => $trendings) {
			if (!empty($trendings)) {
				for ($t=0; $t < sizeof($trendings); $t++) { 
					if (!is_null($trendings[$t]['tweet_volume'])) {
						$trendings[$t]['total'] = $trendings[$t]['tweet_volume'];
						$data[$locationId][] = $trendings[$t];
					}
				}
			}
		}

		$trendings = \app\helpers\TopicsHelper::saveOrUpdateWords($data,$this->topicId);

		
		$trendingTopicsStadistics = \app\helpers\TopicsHelper::saveOrUpdateTopicsStadistics(
			$trendings,
			$this->topicId,
			$this->resourceId
		);

		$trendingsAttachments =  \app\helpers\TopicsHelper::saveOrUpdateAttachments($trendingTopicsStadistics);
		
		
		$trendingsStadistic = \app\helpers\TopicsHelper::saveOrUpdateStadistics($trendingTopicsStadistics);
		
		// if dictionary
		$model = \app\modules\topic\models\MTopics::findOne($this->topicId);

		if ($model->mTopicsDictionaries) {
			$this->searchAndSaveWordsDictionaries($model,$trendingsStadistic);
		}
		
		
	}

	public function searchAndSaveWordsDictionaries($model,$data)
	{
		// get keywors dictionaries
		$words = \app\helpers\TopicsHelper::getKeywordsDictionaries($model);
        // get trendings
        foreach ($data as $index => $values) {
	        for ($d=0; $d <sizeof($values) ; $d++) { 
	         	// replace "#" with space
		        $word_without_hash = \app\helpers\StringHelper::replace($values[$d]['name'],"#"," ");
		        // convert lower case
		        $arr = preg_split('/(?=[A-Z])/',trim($word_without_hash));
		        // join words and trim
		        $sentence = trim(implode($arr," "));
		        // convert to lower case
		        $data[$index][$d]['canonical_name']= $sentence;
	        }
         } 

        // loop dictionaries words in to trends words
		foreach ($words as $keywordId => $word) {
			foreach ($data as $index => $values) {
				for ($v=0; $v < sizeof($values) ; $v++) { 
					$trend = $values[$v]['canonical_name'];
					$isContains = \app\helpers\StringHelper::containsCountIncaseSensitive($trend,$word);
					if ($isContains) {
						$is_words_dictionary_statistic = \app\modules\topic\models\MWordsDictionaryStatistic::find()->where(
							[
								'keywordId' => $keywordId,
								'statisticId'=> $values[$v]['stadisticId']
							]
						)->exists();
						if (!$is_words_dictionary_statistic) {
							$model = new \app\modules\topic\models\MWordsDictionaryStatistic();
							$model->keywordId = $keywordId;
							$model->statisticId = $values[$v]['stadisticId'];
							$model->count = $values[$v]['total'];
						} else {
							$model = \app\modules\topic\models\MWordsDictionaryStatistic::find()->where(
								[
									'keywordId' => $keywordId,
									'statisticId'=> $values[$v]['stadisticId']
								]
							)->one();
							$model->keywordId = $keywordId;
							$model->statisticId = $values[$v]['stadisticId'];
							$model->count = $values[$v]['total'];
						}

						if (!$model->save()) {
							var_dump($model->errors);
						}
						
					}// end if sicontains
				}// end loop for values
			}// ennd loop foreach data
		}// end loop words
       

	}


}
