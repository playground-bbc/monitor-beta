<?php
namespace app\helpers;

use yii;

/**
 * TopicsHelper wrapper for tables function.
 * @author Eduardo Morales <eduardo@montana-studio.com>
 * @group  Montana-Studio LG 
 */

class TopicsHelper
{

	/**
	 * [saveOrUpdateResourceId create or update MTopicResources models]
	 * @param  [type] $resourcesId [description]
	 * @param  [type] $topicId     [description]
	 * @return [type]              [description]
	 */
	public static function saveOrUpdateResourceId($resourcesId,$topicId)
	{
		$istopicResource = \app\modules\topic\models\MTopicResources::find()->where(['topicId' => $topicId])->exists();
		if ($istopicResource) {
			\app\modules\topic\models\MTopicResources::deleteAll('topicId ='.$topicId);
		}
		for ($r=0; $r < sizeof($resourcesId) ; $r++) { 
			$model = new \app\modules\topic\models\MTopicResources();
			$model->topicId = $topicId;
			$model->resourceId = $resourcesId[$r];
			$model->save();
		}
	}
	/**
	 * [saveOrUpdateLocationId create or update MTopicsLocation models]
	 * @param  [type] $locationsId [description]
	 * @param  [type] $topicId     [description]
	 * @return [type]              [description]
	 */
	public static function saveOrUpdateLocationId($locationsId,$topicId)
	{
		$istopicResource = \app\modules\topic\models\MTopicsLocation::find()->where(['topicId' => $topicId])->exists();
		if ($istopicResource) {
			\app\modules\topic\models\MTopicsLocation::deleteAll('topicId ='.$topicId);
		}
		for ($l=0; $l < sizeof($locationsId) ; $l++) { 
			$model = new \app\modules\topic\models\MTopicsLocation();
			$model->topicId = $topicId;
			$model->locationId = $locationsId[$l];
			$model->save();
		}
	}

	/**
	 * [saveOrUpdateDictionaries create or update MDictionaries models]
	 * @param  array  $dictionariesProperty [description]
	 * @return [type]                       [description]
	 */
	public static function saveOrUpdateDictionaries($dictionariesProperty = [])
	{
		$dictionaries = [];
		if (!empty($dictionariesProperty)) {
			for ($d=0; $d < sizeof($dictionariesProperty) ; $d++) { 
				$id = $dictionariesProperty[$d]['id'];
				$model = \app\modules\topic\models\MDictionaries::findOne($id);
				if ($model) {
					$model->name = $dictionariesProperty[$d]['name'];
				}else{
					$model = new \app\modules\topic\models\MDictionaries;
					$model->id = $dictionariesProperty[$d]['id'];
					$model->name = $dictionariesProperty[$d]['name'];
				}
				if ($model->save()) {
					$dictionaries[$model->id] = $model->name;
				}
			}
		}
		return $dictionaries;
	}
	/**
	 * [saveOrUpdateDictionariesWords create or update MDictionaries models]
	 * @param  [type] $content [description]
	 * @return [type]          [description]
	 */
	public static function saveOrUpdateDictionariesWords($content)
	{
		if (!empty($content)) {
			foreach ($content as $dictionaryName => $words) {
				$dictionary = \app\modules\topic\models\MDictionaries::findOne(['name' => $dictionaryName]);
				if ($dictionary) {
					for ($w=0; $w < sizeof($words) ; $w++) {
						$keyword = $words[$w]; 
						$model = \app\modules\topic\models\MKeywords::findOne(
							['dictionaryId' => $dictionary->id,'name' => $keyword]
						);
						if (is_null($model)) {
							$model = new \app\modules\topic\models\MKeywords;
							$model->dictionaryId = $dictionary->id;
							$model->name = $keyword;
							if (!$model->save()) {
								var_dump($model->errors);
								die();
							}
						}
					}
				}else{
					throw new Exception("Error Processing Request: not find dictionary", 1);
					
				}
			}
		}
	}
	/**
	 * [saveOrUpdateTopicsDictionaries create or update MTopicsDictionary models]
	 * @param  array  $sheetIds [description]
	 * @param  [type] $topicId  [description]
	 * @return [type]           [description]
	 */
	public static function saveOrUpdateTopicsDictionaries($sheetIds = [],$topicId)
	{
		if (!empty($sheetIds)) {
			\app\modules\topic\models\MTopicsDictionary::deleteAll('topicId ='.$topicId);
			foreach ($sheetIds as $sheetId) {
				$istopicDictionary = \app\modules\topic\models\MTopicsDictionary::find()->where(
					['topicId' => $topicId,'dictionaryID' => $sheetId]
				)->exists();
				if (!$istopicDictionary) {
					$model = new  \app\modules\topic\models\MTopicsDictionary();
					$model->topicId = $topicId;
					$model->dictionaryID = $sheetId;
					$model->save();
				}
			}
		}
	}
	/**
	 * [saveOrUpdateUrls create or update MUrlsTopics models]
	 * @param  [type] $urls    [description]
	 * @param  [type] $topicId [description]
	 * @return [type]          [description]
	 */
	public static function saveOrUpdateUrls($urls,$topicId)
	{
		$istopicUrls = \app\modules\topic\models\MUrlsTopics::find()->where(['topicId' => $topicId])->exists();
		if ($istopicUrls) {
			\app\modules\topic\models\MUrlsTopics::deleteAll('topicId ='.$topicId);
		}
		for ($u=0; $u < sizeof($urls) ; $u++) { 
			$model = new \app\modules\topic\models\MUrlsTopics();
			$model->topicId = $topicId;
			$model->url = $urls[$u];
			$model->save();
		}
	}

	/**
	 * [getTopicsByResourceName get  topic related with resource name]
	 * @param  [string] $resourceName [name resource]
	 * @return [array]               [topics]
	 */
	public static function getTopicsByResourceName($resourceName)
	{
		$topics = \app\modules\topic\models\MTopics::find()->where(['status' => 1])->with(
			[
				'mTopicResources.resource' => function ($query) use($resourceName)
				{
					$query->andWhere(['name' => $resourceName]);
				}
			]
		)->asArray()->all();

		$results = [];

		for ($t=0; $t < sizeof($topics); $t++) { 
			if (\yii\helpers\ArrayHelper::keyExists('mTopicResources',$topics[$t])) {
				if (!is_null($topics[$t]['mTopicResources'])) {
					$mTopicResources = \yii\helpers\ArrayHelper::remove($topics[$t],'mTopicResources');
					for ($m=0; $m < sizeof($mTopicResources) ; $m++) { 
						if (!is_null($mTopicResources[$m]['resource'])) {
							$resource = \yii\helpers\ArrayHelper::remove($mTopicResources[$m],'resource');
							$topics[$t]['resource'] = $resource;
							$results[] = $topics[$t];
						}
					}// end loop for
				}// end if
			}
		}// end loop for


		return $results;
	}
	/**
	 * [saveOrUpdateWords save words in the table words: words are trending, term or hastag related in the searcg]
	 * @param  [type] $data    [description]
	 * @param  [type] $topicId [description]
	 * @return [type]          [description]
	 */
	public static function saveOrUpdateWords($data,$topicId)
	{
		foreach ($data as $key => $values) {
			for ($t=0; $t <sizeof($values) ; $t++) { 
				if ($values[$t]['name'] != '') {
					$is_word = \app\modules\topic\models\MWords::find()->where(
						[
							'topicId' => $topicId,
							'name' => $values[$t]['name'],
						]
					)->exists();
					if (!$is_word) {
						$model = new \app\modules\topic\models\MWords();
						$model->topicId = $topicId;
						$model->name = $values[$t]['name'];
						if ($model->save()) {
							$data[$key][$t]['wordId']= $model->id;
						}
					}else{

						$model = \app\modules\topic\models\MWords::find()->where(
							[
								'topicId' => $topicId,
								'name' => $values[$t]['name'],
							]
						)->one();
						$data[$key][$t]['wordId']= $model->id;

					}// end if !word
				}// end loop for
			}
		}// end loop foreach
		return $data;
	}
	/**
	 * [saveOrUpdateTopicsStadistics save or update TopicsStadistics]
	 * @param  [type] $data       [description]
	 * @param  [type] $topicId    [description]
	 * @param  [type] $resourceId [description]
	 * @return [type]             [description]
	 */
	public static function saveOrUpdateTopicsStadistics($data,$topicId,$resourceId,$location=true)
	{
		if ($topicId) {
			foreach ($data as $key => $values) {
				for ($t=0; $t < sizeof($values) ; $t++) {
					if (isset($values[$t]['wordId'])) {
						$is_topic_stadistics = \app\modules\topic\models\MTopicsStadistics::find()->where(
							[
								'topicId' => $topicId,
								'resourceId' => $resourceId,
								'locationId' => ($location)? $key: null,
								'wordId' => $values[$t]['wordId'],
							]
						)->exists();
						if (!$is_topic_stadistics) {
							$model =  new \app\modules\topic\models\MTopicsStadistics();
							$model->topicId = $topicId;
							$model->resourceId = $resourceId;
							$model->locationId = ($location)? $key: null;
							$model->wordId = $values[$t]['wordId'];

							if($model->save()){
								$data[$key][$t]['topicStadisticId'] = $model->id;
							}else{
								var_dump($model->errors);
							}

						} else {
							$model = \app\modules\topic\models\MTopicsStadistics::find()->where(
								[
									'topicId' => $topicId,
									'resourceId' => $resourceId,
									'locationId' => ($location)? $key: null,
									'wordId' => $values[$t]['wordId'],
								]
							)->one();

							$data[$key][$t]['topicStadisticId'] = $model->id;
						}
					}
				}
			}
		}

		return $data;
	}
	/**
	 * [saveOrUpdateStadistics save or update Stadistics]
	 * @param  [type] $data [description]
	 * @return [type]       [description]
	 */
	public static function saveOrUpdateStadistics($data)
	{
		foreach ($data as $key => $values) {
			for ($t=0; $t < sizeof($values); $t++) { 
				if (isset($values[$t]['topicStadisticId']) && !is_bool($values[$t]['total'])) {
					$is_stadistics = \app\modules\topic\models\MStatistics::find()->where(
						[
							'topicStaticId' => $values[$t]['topicStadisticId'],
							'timespan' => \app\helpers\DateHelper::getTodayDate()
						]
					)->exists();

					if (!$is_stadistics) {
						$model = new \app\modules\topic\models\MStatistics();
						$model->topicStaticId = $values[$t]['topicStadisticId'];
						$model->total = $values[$t]['total'];
						$model->timespan =\app\helpers\DateHelper::getTodayDate();

						if($model->save()){
							$data[$key][$t]['stadisticId'] = $model->id;
						}else{
							var_dump($values[$t]);
							var_dump($model->errors);
						}


					} else {
						$model = \app\modules\topic\models\MStatistics::find()->where(
							[
								'topicStaticId' => $values[$t]['topicStadisticId'],
								'timespan' => \app\helpers\DateHelper::getTodayDate()
							]
						)->one();
						$model->total = $values[$t]['total'];

						if($model->save()){
							$data[$key][$t]['stadisticId'] = $model->id;
						}
					}
				}
			}
		}
		return $data;
	}
	/**
	 * [saveOrUpdateAttachments save or update Attachments]
	 * @param  [type] $data [description]
	 * @return [type]       [description]
	 */
	public static function saveOrUpdateAttachments($data)
	{
		foreach ($data as $key => $values) {
			for ($v=0; $v < sizeof($values) ; $v++) { 
				if (isset($values[$v]['topicStadisticId'])) {
					$is_attachments = \app\modules\topic\models\MAttachments::find()->where(
					[
						'topicStatisticId' => $values[$v]['topicStadisticId'],
						'src_url' => $values[$v]['url'],
					]
					)->exists();

					if (!$is_attachments) {
						$model = new \app\modules\topic\models\MAttachments();
						$model->topicStatisticId = $values[$v]['topicStadisticId'];
						$model->src_url = $values[$v]['url'];
						$model->save();
					}else{
						$model = \app\modules\topic\models\MAttachments::find()->where(
							[
								'src_url' => $values[$v]['url'],
							]
						)->one();
					}
					// get id attachments
					$data[$key][$v]['attachmentId'] = $model->id;
				}
			}
		}
		return $data;
	}
	/**
	 * [getKeywordsDictionaries get words from dictionaries key = wordId value= word]
	 * @param  [type] $model [description]
	 * @return [type]        [description]
	 */
	public static function getKeywordsDictionaries($model)
	{
		$words = [];
		foreach ($model->mTopicsDictionaries as $mTopicDictionarie) {
            foreach ($mTopicDictionarie->dictionary->mKeywords as $kewords) {
                $words[$kewords->id] = \app\helpers\StringHelper::lowercase($kewords->name);
            }
        }
        return $words;
	}
	/**
	 * [checkFinalTimeTopic check the end date of the topic if the end date has already been met, change the status]
	 * @param  array  $topic [current topic]
	 */
	public static function checkFinalTimeTopic($topic = [])
	{
		if (isset($topic['end_date'])) {
			date_default_timezone_set('UTC');
			$end_date = intval($topic['end_date']);
			$today_date = \app\helpers\DateHelper::getTodayDate(false);
			
			if ($today_date->getTimestamp() > $end_date) {
				$model = \app\modules\topic\models\MTopics::findOne($topic['id']);
				if (!is_null($model)) {
					$model->status = 0;
					$model->resourceId = $topic['resource']['id'];
					if (!$model->save()) {
						var_dump($model->errors);
					}
				}
			}
		}
	}

	/**
	 * [orderSeries order  data to graph]
	 * @param  [type] $mStadistics [description]
	 * @param  [type] $period      [description]
	 * @return [type]              [description]
	 */
	public static function orderSeries($mStadistics,$period)
	{
		$stadistics = [];

		if (!empty($mStadistics) && !empty($period)) {
			for ($m=0; $m <sizeof($mStadistics) ; $m++) { 
				$stadistics[$m]['name'] = $mStadistics[$m]['name'];
				
				for ($p=0; $p < sizeof($period) ; $p++) { 
					
					$stadistics[$m]['data'][$p] = null;
				}

				for ($d=0; $d < sizeof($mStadistics[$m]['data']) ; $d++) { 
					$clave = array_search($mStadistics[$m]['data'][$d]['date'], $period);
					//var_dump($clave);
					if (!is_bool($clave)) {
						$stadistics[$m]['data'][$clave] += (int) $mStadistics[$m]['data'][$d]['total'];
					}
				}
			}
		}
		return $stadistics;
	}
	/**	 
	* [getAttributesForDetailView compose detailView array if there url on topic]
	* @param  [obj] $model [topic  model]
	* @return [array]              [arraty detailView]
	*/
	public static function getAttributesForDetailView($model)
	{
		$urls = \app\modules\topic\models\MUrlsTopics::find()->where(['topicId' => $model->id])->all();
		$url_detail_arr = [];
		if (!empty($urls)) {
			$url_detail_arr = [
				'label' => Yii::t('app','Scraping Paginas Web Urls'),
                'format'    => 'raw',
                //'attribute' => 'resourceId',
                'value' => function() use($urls) {
                    $html = '';
                    foreach ($urls as $webPage) {
                        $html .= " <span class='label label-success'><a style='color: white;' href='{$webPage->url}' target='_blank'>{$webPage->url}</a></span>";
                    }
                    return $html;
                }

			];
		}

		$detail_attributes = [
            [
                'label' => Yii::t('app','Estado'),
                'format'    => 'raw',
                'attribute' => 'status',
                'value' => function($model) {
                    return ($model->status) ? 'Active' : 'Inactive';
                }
            ],
            'name',
            [
                'label' => Yii::t('app','Fecha Final'),
                'format'    => 'raw',
                'attribute' => 'end_date',
                'value' => function($model) {
                    date_default_timezone_set('UTC');
                    return date('Y-m-d',$model->end_date);
                }
            ],
            [
                'label' => Yii::t('app','Recursos Sociales'),
                'format'    => 'raw',
                'attribute' => 'resourceId',
                'value' => function($model) {
                    $html = '';
                    foreach ($model->mTopicResources as $topicResource) {
                        $html .= " <span class='label label-info'>{$topicResource->resource->name}</span>";
                    }
                    return $html;
                },

            ],
        ];

        if (!empty($url_detail_arr)) {
        	array_push($detail_attributes, $url_detail_arr);
        }

        return $detail_attributes;
	}
}