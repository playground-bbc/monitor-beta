<?php
namespace app\modules\topic\models\api;


/**
 * 
 */
class TopicBaseApi
{
	/**
	 * [callResourcesApiTopic call function if exist in the class]
	 * @param  [type] $topics [description]
	 * @return [type]         [description]
	 */
	public function callResourcesApiTopic($topics){
		
		$resources = [];
		for ($t=0; $t <sizeof($topics) ; $t++) { 
			if (!empty($topics[$t]['resource'])) {
				$resourceName = \app\helpers\StringHelper::replace($topics[$t]['resource']['name']," ","");
				if (method_exists($this, $resourceName.'Api')) {
					$method = "${resourceName}Api";
					$resources[$method][] = $topics[$t];
					\app\helpers\TopicsHelper::checkFinalTimeTopic($topics[$t]);
				}
			}
		}
		foreach($resources as $method => $topics){
			$this->{$method}($topics);
		}
	}
	/**
	 * [TwitterApi functions call to Twitter Api ]
	 * @param array $topics [description]
	 */
	public function TwitterApi($topics = []){
		$trending = new \app\modules\topic\models\api\TwitterApi();
		foreach ($topics as $topic) {
			if ($trending->prepare($topic)) {
				$trends = $trending->callTrendings();
				if (!empty($trends)) {
					$trending->saveData();
				}
			}
		}
	}

	/**
	 * [InstagramApi functions call to Scraping top Hastag and Instagram Page]
	 * @param array $topics [description]
	 */
	public function InstagramApi($topics = []){
		$hashtag = new \app\modules\topic\models\api\InstagramScraping();
		foreach ($topics as $topic) {
			if ($hashtag->prepare($topic)) {
				if ($hashtag->callHashtag()) {
					$hashtag->saveData();
				}
			}
		}
	}

	/**
	 * [PaginasWebApi functions call to Scraping url from Web Pages ]
	 * @param array $topics [description]
	 */
	public function PaginasWebsApi($topics = []){

		$webScraping = new \app\modules\topic\models\api\WebScraping();
		foreach ($topics as $topic) {
			if ($webScraping->prepare($topic)) {
				$crawlers = $webScraping->getRequest();
				if (!empty($crawlers)) {
					$content = \app\helpers\ScrapingHelper::getContent($crawlers);
					$data     = \app\helpers\ScrapingHelper::setContent($content);
					$groupContentData = $webScraping->groupContentData($data);
					$analisysText = $webScraping->setAnalisys($groupContentData);
					if ($analisysText) {
						$webScraping->saveData($analisysText);
					}
				}
			}
		}
	}	
}

?>