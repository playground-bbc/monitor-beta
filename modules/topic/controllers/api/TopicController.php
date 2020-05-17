<?php

namespace app\modules\topic\controllers\api;

use yii\rest\Controller;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use yii\web\Response;
use yii\web\NotFoundHttpException;


use \app\modules\topic\models\MTopics;
use \app\modules\topic\models\MTopicsStadistics;
use \app\modules\topic\models\MStatistics;

/**
 * 
 */
class TopicController extends Controller
{
	/**
	 * [behaviors negotiator to return the response in json format]
	 * @return [array] [for controller]
	 */
	public function behaviors(){
	   return [
	        [
	            'class' => 'yii\filters\ContentNegotiator',
	            'only' => [
	            	'numbers-resources',
	            	'cloud-word',
	            	'cloud-dictionaries',
	            	'stadistics-date',
	            ],  // in a controller
	            // if in a module, use the following IDs for user actions
	            // 'only' => ['user/view', 'user/index']
	            'formats' => [
	                'application/json' => Response::FORMAT_JSON,
	            ],
	            'languages' => [
	                'en',
	                'de',
	            ],
	        ],
	   ];
	}

	/**
	 * [actionNumbersResources returns the number of entities with topic_stadistic]
	 * @param  [type] $topicId    [description]
	 * @return [array] []
	 */
	public function actionNumbersResources($topicId)
	{
		$topic = MTopics::find()->where(['id' => $topicId])->with([
			'sources'=> function ($query) use ($topicId)
			{
				$query->select(['id','name'])->with([
					'mTopicsStadistics' => function ($query) use ($topicId)
					{
						$query->andWhere(['topicId' => $topicId]);
					}
				]);
			},
		])->asArray()->one();
				
        return [
        	'topic' => $topic
        ];
	}
	/**
	 * [actionCloudWord return array words from topics]
	 * @param  [type] $topicId    [description]
	 * @param  [type] $resourceId [description]
	 * @return [type]             [description]
	 */
	public function actionCloudWord($topicId,$resourceId)
	{
		$model = MTopicsStadistics::find()->where(
			[
				'topicId' => $topicId,
				'resourceId' => $resourceId,
			]
		)->with(
			[
				'word',
				'mStatistics' => function ($query)
				{
					date_default_timezone_set('UTC');
					$timespan = \app\helpers\DateHelper::getTodayDate();
					$query->andWhere(['>=','timespan',$timespan]);
				},
				'mAttachments'
			]
		)->asArray()->all();


		$words = [];
		$index = 0;

		if (!is_null($model)) {
			for ($i=0; $i < sizeof($model); $i++) { 
				if (!empty($model[$i]['mStatistics'])) {
				 	$words[$index] = [
				 		'text' => $model[$i]['word']['name'],
				 		'weight' => 0,
						'url' => $model[$i]['mAttachments'][0]['src_url'],
				 	];
				 	for ($s=0; $s < sizeof($model[$i]['mStatistics']); $s++) { 
				 		$words[$index]['weight'] += $model[$i]['mStatistics'][$s]['total'];
				 	}
				 	$index++;
				 } 
			}
		}


		return [
        	'words' => $words
        ];
	}

	/**
	 * [actionCloudDictionaries return array the words dictionaries for topic]
	 * @param  [int] $topicId [description]
	 * @return [array]          [description]
	 */
	public function actionCloudDictionaries($topicId)
	{
		$model = $this->findModel($topicId);
		$mTopicsDictionaries = [];
		if ($model->mTopicsDictionaries) {
			foreach ($model->mTopicsStadistics as $topicsStadistic) {
				foreach ($topicsStadistic->mStatistics as $stadistic) {
					if ($stadistic->mWordsDictionaryStatistics) {
						//$tmp = [];
						foreach ($stadistic->mWordsDictionaryStatistics as $dictionaryStatistics) {
							if (!isset($mTopicsDictionaries[$dictionaryStatistics->keyword->name])) {
								$mTopicsDictionaries[$dictionaryStatistics->keyword->name] = $dictionaryStatistics->count;
							}else{
								$mTopicsDictionaries[$dictionaryStatistics->keyword->name] += $dictionaryStatistics->count;
							}
							
						}
					}
				}
			}
		}
		$wordsStadistic = [];
		if (!empty($mTopicsDictionaries)) {
			foreach ($mTopicsDictionaries as $word => $total) {
				$wordsStadistic[] = [
					'text' => $word,
					'weight' =>  \Yii::$app->formatter->asDecimal($total),
				];
			}
		}

		return [
			'model' => $wordsStadistic
		];
	}
	
	public function actionStadisticsDate($topicId)
	{
		$model = $this->findModel($topicId);

		//$expression = new Expression("DATE(FROM_UNIXTIME(timespan)) AS date,total");
		$expression = new Expression("timespan AS date,total");
		$expressionGroup = new Expression("DATE(FROM_UNIXTIME(timespan))");
		
		$mStadistics = [];
		$mWord = [];
		$seriesWords = [];

		//$end_date = \app\helpers\DateHelper::sub($model->end_date,'+1 days');
		$end_date = $model->end_date;
		
		$period = \app\helpers\DateHelper::periodDates($model->createdAt,$end_date);

		if ($model->mTopicsStadistics) {
			foreach ($model->mTopicsStadistics as $topicsStadistic) {
				if ($topicsStadistic->mStatistics) {
					$data = [];
					foreach ($topicsStadistic->mStatistics as $index  => $stadistic) {
						$date_utc = \Yii::$app->formatter->asDatetime($stadistic->timespan,'yyyy-MM-dd');
						$data[$index]['date'] =  $date_utc;
						$data[$index]['total'] =  $stadistic->total;
					}
					$mStadistics[] = [
						'name' => $topicsStadistic->word->name,
						'data' => $data
					];
				}
				
			}// end loop mTopicsStadistics
		}// end if

		// reoorder data
		\yii\helpers\ArrayHelper::multisort($mStadistics, ['total', 'name'], [SORT_ASC, SORT_DESC]);
		$seriesWords = \app\helpers\TopicsHelper::orderSeries($mStadistics,$period);
		

		return [
			'seriesWords' => $seriesWords,
			'period' => $period,
		];
	}


	/**
     * Finds the MTopics model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return MTopics the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = MTopics::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(\Yii::t('app', 'The requested page does not exist.'));
    }
}
