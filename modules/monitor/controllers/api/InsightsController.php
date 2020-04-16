<?php

namespace app\modules\monitor\controllers\api;

use yii\rest\Controller;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use yii\web\Response;
use yii\web\NotFoundHttpException;


/**
 * class controller to Api widget
 */
class InsightsController extends Controller
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
	            	'numbers-content',
	            	'content-page',
	            	'posts-insights',
	            	'storys-insights'
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
	 * [actionNumbersContent returns the number of entities]
	 * @return [array] [entity type with its id: page, post, storys]
	 */
	public function actionNumbersContent()
	{
		$pageContentId = \app\models\WTypeContent::find()->select(['id'])->where(['name' => 'Page'])->one(); 

        $page_resource = \app\models\WContent::find()->select('resource_id')->where(['type_content_id' => $pageContentId->id])->groupBy('resource_id')->asArray()->all();
        
        return $page_resource;
	}
	/**
	 * [actionContentPage returns the information on the page with its Insights]
	 * @param  [int] $resourceId [id of resource Facebook o Instagram]
	 * @return [array]           [Page data]
	 */
	public function actionContentPage($resourceId)
	{
		$pageContentId = \app\models\WTypeContent::find()->select(['id'])->where(['name' => 'Page'])->one(); 
		$page_content = \app\models\WContent::find()->where(
			[
				'type_content_id' => $pageContentId->id,
				'resource_id' => $resourceId
			]
		)->with(['resource'])->orderBy(['updatedAt' => SORT_DESC])->asArray()->all();

		for ($p=0; $p < sizeof($page_content) ; $p++) { 

        	$insights = \app\models\WInsights::find()->where(['content_id' => $page_content[$p]['id']])->orderBy(['end_time' => SORT_DESC ])->asArray()->groupBy('name')->limit(5)->all();
        	if (!is_null($insights)) {
        		$page_content[$p]['wInsights'] = $insights;
        	}
        }
		
		return reset($page_content);
	}
	/**
	 * [actionPostsInsights returns the information on the Post with its Insights]
	 * @param  [int] $resourceId [id of resource Facebook o Instagram]
	 * @return [array]           [Post data]
	 */
	public function actionPostsInsights($resourceId)
	{
		// type posts
        $postContentId = \app\models\WTypeContent::find()->select(['id'])->where(['name' => 'Post'])->one(); 
        // last five
        $posts_content = \app\models\WContent::find()->where(
            [
                'type_content_id' => $postContentId->id,
                'resource_id' => $resourceId // get by source
            ]
        )->with(['resource'])->orderBy(['updatedAt' => SORT_DESC])->asArray()->limit(5)->all();

        

        for ($p=0; $p < sizeof($posts_content) ; $p++) { 

        	$insights = \app\models\WInsights::find()->where(['content_id' => $posts_content[$p]['id']])->orderBy(['end_time' => SORT_DESC ])->asArray()->groupBy('name')->limit(3)->all();
        	if (!is_null($insights)) {
        		$posts_content[$p]['wInsights'] = $insights;
        	}
        }

        return $posts_content;
	}
	/**
	 * [actionStorysInsights returns the information on the Storys with its Insights]
	 * @param  [int] $resourceId [id of resource Facebook o Instagram]
	 * @return [array]           [Storys data]
	 */
	public function actionStorysInsights($resourceId)
	{
		$storyContentId = \app\models\WTypeContent::find()->select(['id'])->where(['name' => 'Story'])->one();

        $storys_content = \app\models\WContent::find()->where(
            [
                'type_content_id' => $storyContentId->id,
                'resource_id' => $resourceId // get by source
            ]
        )->with(['resource'])->orderBy(['updatedAt' => SORT_DESC])->asArray()->limit(5)->all();

        for ($p=0; $p < sizeof($storys_content) ; $p++) { 

        	$insights = \app\models\WInsights::find()->where(['content_id' => $storys_content[$p]['id']])->orderBy(['end_time' => SORT_DESC ])->asArray()->groupBy('name')->limit(3)->all();
        	if (!is_null($insights)) {
        		$storys_content[$p]['wInsights'] = $insights;
        	}
        }

        return $storys_content;
	}
}