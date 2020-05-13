<?php
namespace app\modules\topic\models\api;

use Yii;


/**
 * wrapper WebScraping for topics
 */
class WebScraping{

	public $topicId;
	public $userId;
	public $end_date;
	public $resourceId;
	public $remaining = 10;
	public $data;
	public $urls;

	public function prepare($topic)
	{
		$this->topicId = $topic['id'];
		$this->userId = $topic['userId'];
		$this->end_date = $topic['end_date'];
		$this->resourceId = $topic['resource']['id'];
		// get url
		$topic = \app\modules\topic\models\MTopics::find()->where(
			[
				'id' => $this->topicId
			]
		)->with('mUrlsTopics')->asArray()->one();
		
		$mUrlsTopics = \yii\helpers\ArrayHelper::map($topic['mUrlsTopics'],'id','url');
		$this->urls = \app\helpers\StringHelper::getValidUrls($mUrlsTopics);
		
		return (!empty($this->urls)) ? true: false;
	}

	public function getRequest()
	{
		// get all sub links by each url
		$urls = \app\helpers\ScrapingHelper::getLinksInUrlsWebPage($this->urls);
		// get the crawlers
		$crawlers = \app\helpers\ScrapingHelper::getRequest($urls);

		return $crawlers;
	}

	public function groupContentData($data)
	{
		$groupContentData = [];

		foreach ($data as $url => $values) {
			foreach ($values as $link => $nodes) {
				$content = '';
				for ($n=0; $n < sizeof($nodes) ; $n++) {
					$content.= " ".$nodes[$n];
				}
				$groupContentData[$link][] = $content;
			}
		}
		return $groupContentData;
	}

	public function setAnalisys($groupContentData)
	{
		$analisysText = [];
		foreach ($groupContentData as $link => $contentData) {
			for ($c=0; $c <sizeof($contentData) ; $c++) { 
				$multipartForm =  \app\helpers\ScrapingHelper::composeMultipartForm($contentData[$c]);
				$analisysText[$contentData[$c]] = \app\helpers\ScrapingHelper::sendTextAnilysis($multipartForm,$link);
			}
		}
		return $analisysText;
	}


	public function saveData($analisysText = [])
	{

		$trendingsWebPage = \app\helpers\TopicsHelper::saveOrUpdateWords($analisysText,$this->topicId);

		$trendingTopicsStadistics = \app\helpers\TopicsHelper::saveOrUpdateTopicsStadistics(
			$trendingsWebPage,
			$this->topicId,
			$this->resourceId,
			false
		);

		$trendingsAttachments =  \app\helpers\TopicsHelper::saveOrUpdateAttachments($trendingTopicsStadistics);

		$trendingsStadistic = \app\helpers\TopicsHelper::saveOrUpdateStadistics($trendingTopicsStadistics);
		
		// if dictionary
		$model = \app\modules\topic\models\MTopics::findOne($this->topicId);

		if ($model->mTopicsDictionaries) {

			$this->searchAndSaveWordsDictionaries($model,$trendingsStadistic);
		}
	}

	public function searchAndSaveWordsDictionaries($model,$trendingsStadistic)
	{
		
		// get keywors dictionaries
		$words = \app\helpers\TopicsHelper::getKeywordsDictionaries($model);
		// loop dictionaries words in to trends words
		foreach ($words as $keywordId => $word) {
			foreach ($trendingsStadistic as $sentence => $values) {
				if (sizeof($values)) {
					$isContains = \app\helpers\StringHelper::containsCountIncaseSensitive($sentence,$word);
					if (isset($values[0]['stadisticId'])) {
						$statisticId = $values[0]['stadisticId'];
						if ($isContains) {

							$is_words_dictionary_statistic = \app\modules\topic\models\MWordsDictionaryStatistic::find()->where(
									[
										'keywordId' => $keywordId,
										'statisticId'=> $statisticId
									]
								)->exists();
							if (!$is_words_dictionary_statistic) {
								$model = new \app\modules\topic\models\MWordsDictionaryStatistic();
								$model->keywordId = $keywordId;
								$model->statisticId = $statisticId;
								$model->count = $isContains;
							}else{
								$model = \app\modules\topic\models\MWordsDictionaryStatistic::find()->where(
										[
											'keywordId' => $keywordId,
											'statisticId'=> $statisticId
										]
								)->one();
								$model->keywordId = $keywordId;
								$model->statisticId = $statisticId;
								$model->count = $isContains;
							}
							if (!$model->save()) {
								var_dump($model->errors);
							}
						}
					}
				}
			}
		}
	}

}