<?php

namespace app\modules\insights\controllers;

use yii\rest\Controller;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use yii\web\Response;
use yii\web\NotFoundHttpException;


/**
 * class controller to Api widget
 */
class DefaultController extends Controller
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
        $page_resource = \app\helpers\InsightsHelper::getNumbersContent();
        return $page_resource;
	}
	/**
	 * [actionContentPage returns the information on the page with its Insights]
	 * @param  [int] $resourceId [id of resource Facebook o Instagram]
	 * @return [array]           [Page data]
	 */
	public function actionContentPage($resourceId)
	{
		return \app\helpers\InsightsHelper::getContentPage($resourceId);
	}
	/**
	 * [actionPostsInsights returns the information on the Post with its Insights]
	 * @param  [int] $resourceId [id of resource Facebook o Instagram]
	 * @return [array]           [Post data]
	 */
	public function actionPostsInsights($resourceId)
	{
        // last five
        $posts_content = \app\helpers\InsightsHelper::getPostsInsights($resourceId);
        return \app\helpers\InsightsHelper::getPostInsightsByResource($posts_content,$resourceId);
	}
	/**
	 * [actionStorysInsights returns the information on the Storys with its Insights]
	 * @param  [int] $resourceId [id of resource Facebook o Instagram]
	 * @return [array]           [Storys data]
	 */
	public function actionStorysInsights($resourceId)
	{
		return \app\helpers\InsightsHelper::getStorysInsights($resourceId);
	}
}