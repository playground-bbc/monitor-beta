<?php
namespace app\modules\topic\models\api;

use Yii;


/**
 * wrapper Instagram for topics
 */
class InstagramScraping
{
	public $topicId;
	public $userId;
	public $end_date;
	public $resourceId;
	public $limit = 15;
	public $hashtag;
	public $data;

	public function prepare($topic)
	{
		$this->topicId = $topic['id'];
		$this->userId = $topic['userId'];
		$this->end_date = $topic['end_date'];
		$this->resourceId = $topic['resource']['id'];
		$this->hashtag = $this->getSrapingHastag();
		
		return (!empty($this->hashtag)) ? true: false;
		
	}

	public function getSrapingHastag()
	{
		// Initialize the client with the handler option
		$client = new \GuzzleHttp\Client();
		$request = new \GuzzleHttp\Psr7\Request('get', "https://tophashtags.net/trending-instagram-hashtags/");
		$response = $client->send($request, ['timeout' => 10]);

		$code = $response->getStatusCode();
		$reason = $response->getReasonPhrase(); // OK

		$elements = [];
		if ($code == 200 && $reason == 'OK') {
			$body = $response->getBody()->getContents();
			$crawler = new \Symfony\Component\DomCrawler\Crawler($body);
			$count = $crawler->filter('.item-hashtag')->count(); 
			if ($count) {
				$elements = $crawler->filter('.item-hashtag')->each(function($node){
				    return \app\helpers\StringHelper::replace(trim($node->text()),"#","");
				});
			}
		}

		//limit hastag
		$hashtag = [];
		for ($i=0; $i < $this->limit; $i++) { 
			$hashtag[] = $elements[$i];
		}
		
		
		return $hashtag;
	}


	public function callHashtag()
	{
		// Initialize the client with the handler option
		$client = new \GuzzleHttp\Client([
			'verify' => false,
			'curl' => [
		        CURLMOPT_MAX_HOST_CONNECTIONS => 1,
		        CURLOPT_SSLVERSION => CURL_SSLVERSION_TLSv1_2,
		        CURLOPT_SSL_VERIFYPEER => false,
		        CURLOPT_BUFFERSIZE => 120000
		    ],
		]);
		$model =[];

		for ($h=0; $h < sizeof($this->hashtag); $h++) {
			try {

				$url = "https://www.instagram.com/explore/tags/{$this->hashtag[$h]}/";
				$response = $client->request('GET',$url);
			
				$code = $response->getStatusCode();
				$reason = $response->getReasonPhrase(); // OK

				if ($code == 200 && $reason == 'OK') {
					$body = $response->getBody()->getContents();
					$crawler = new \Symfony\Component\DomCrawler\Crawler($body);
					$count = $crawler->filterXpath('//script[@type="text/javascript"]')->count();
					if ($count) {
						$elements = $crawler->filterXpath('//script[@type="text/javascript"]')->eq(3)->text();
						$json_string = \app\helpers\StringHelper::replace($elements,"window._sharedData = ","");
						$json_string = \app\helpers\StringHelper::replace($json_string,";","");
						$data =  str_replace("\u0022","\\\\\"",json_decode($json_string,JSON_HEX_QUOT)); 
						$model['name'] = $data['entry_data']['TagPage'][0]['graphql']['hashtag']['name'];
						$model['canonical_name'] = "#".$data['entry_data']['TagPage'][0]['graphql']['hashtag']['name'];
						$model['url'] = $url;
						$model['total'] = $data['entry_data']['TagPage'][0]['graphql']['hashtag']['edge_hashtag_to_media']['count'];

						$this->data[] = $model;
					}
				}
				
			} catch (GuzzleHttp\Exception\RequestException $e) {
				continue;
			}
		}
		return (!empty($this->data)) ? true: false;
	}


	public function saveData()
	{
		$data = [];
		if (!empty($this->data)) {
			$data[] = $this->data;
			$trendings = \app\helpers\TopicsHelper::saveOrUpdateWords($data,$this->topicId);
			$hashTagTopicsStadistics = \app\helpers\TopicsHelper::saveOrUpdateTopicsStadistics(
				$trendings,
				$this->topicId,
				$this->resourceId,
				false
			);
			$hashTagAttachments =  \app\helpers\TopicsHelper::saveOrUpdateAttachments($hashTagTopicsStadistics);
			

			$hashTagStadistic = \app\helpers\TopicsHelper::saveOrUpdateStadistics($hashTagTopicsStadistics);
		
			
			// if dictionary
			$model = \app\modules\topic\models\MTopics::findOne($this->topicId);

			if ($model->mTopicsDictionaries) {
				$this->searchAndSaveWordsDictionaries($model,$hashTagStadistic);
			}
		}
		
	}

	public function searchAndSaveWordsDictionaries($model,$data)
	{
		// get keywors dictionaries
		$words = \app\helpers\TopicsHelper::getKeywordsDictionaries($model);

		// loop dictionaries words in to trends words
		foreach ($words as $keywordId => $word) {
			foreach ($data as $index => $values) {
				for ($v=0; $v < sizeof($values) ; $v++) { 
					$trend = $values[$v]['name'];
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

?>	