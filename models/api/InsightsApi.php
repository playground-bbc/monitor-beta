<?php
namespace app\models\api;

use Yii;
use yii\base\Model;

/**
 *  class wrapper to call facebook api to get Insights
 */
class InsightsApi extends Model
{
	private $_userId;
	private $_access_token;
	
	private $_business_id;
	private $_business_name;
	private $_business_id_instagram;

	private $_appsecret_proof;
	private $_limit = 5;
	

	private $_postIds = [];
	

	/**
	 * [prepare set class variables]
	 * @param  [type] $user [description]
	 * @return [type]       [description]
	 */
	public function prepare($user)
	{
		if (yii\helpers\ArrayHelper::keyExists('tasks',$user['credencial'])) {
			$this->_userId = $user['user_id'];
			$this->_access_token = $user['credencial']['access_token'];
			$this->_business_name = $user['credencial']['name'];
			$this->_business_id = $user['credencial']['id'];
			$this->_appsecret_proof = \app\helpers\FacebookHelper::getAppsecretProof($this->_access_token);

			return true;
		}
		return false;
	}
	/**
	 * [call api to get insights of the page]
	 * @return [array]       [page insigth]
	 */
	public function getInsightsPageFacebook()
	{
		
		$today  = \app\helpers\DateHelper::getToday();
		
		$end_point = "{$this->_business_id}?fields=id,fan_count,cover,link,about,picture{url},insights.metric(page_impressions,page_impressions_unique,page_post_engagements).since({$today}).until({$today}).period(day)";

		$params = [
            'access_token' => $this->_access_token,
            'appsecret_proof' => $this->_appsecret_proof
        ];

        return \app\helpers\InsightsHelper::getData($end_point,$params);

	}

	/**
	 * [set the conten in db and his insigth]
	 * @param [array]       [page insigth]
	 */
	public function setInsightsPageFacebook($page)
	{
		$typeContent = \app\models\WTypeContent::find()->select('id')->where(['name' => 'Page'])->one();
		$resource    = \app\models\Resources::find()->select('id')->where(['name' => 'Facebook Comments'])->one();
		$pageId      = (\yii\helpers\ArrayHelper::keyExists('id',$page))? $page['id'] : null;

		if (!is_null($typeContent) && !is_null($resource) && !is_null($pageId)) {
			// if there content
			$where =[
				'type_content_id' => $typeContent->id,
				'resource_id'     => $resource->id,
				'content_id'      => $pageId,
			];

			$properties = [
				'message'   => $page['about'],
				'permalink' => $page['link'],
				'image_url' => $page['picture']['data']['url'],
				'timespan'  => \app\helpers\DateHelper::getToday(),
			];

			$content = \app\helpers\InsightsHelper::saveContent($where,$properties);
			
			if ($content) {
				$insights = $page['insights']['data'];
				if (!empty($page['fan_count'])) {
					$name = 'fan_count';
					$period = 'lifetime';
					$title = 'Usuarios a quienes les gusta la página';
					$description = 'El número de usuarios a los que les gusta la página.';
					$value = $page['fan_count'];
					$metric = \app\helpers\InsightsHelper::setMetric($name,$period,$value,$title,$description);
					array_push($insights, $metric);
				}
				\app\helpers\InsightsHelper::saveInsightsPage($insights,$content->id);
			}

		}
	}
	/**
	 * [call api to get insights of the page]
	 * @return [array]       [page insigth]
	 */
	public function setInsightsPostFacebook()
	{
		$typeContent = \app\models\WTypeContent::find()->select('id')->where(['name' => 'Post'])->one();
		$resource    = \app\models\Resources::find()->select('id')->where(['name' => 'Facebook Comments'])->one();


		if (!is_null($typeContent) && !is_null($resource)) {
			
			$end_point = "{$this->_business_id}/published_posts?fields=id,permalink_url,updated_time,message,picture,attachments{media,media_type,subattachments,title},insights.metric(post_impressions,post_engaged_users,post_reactions_by_type_total,page_actions_post_reactions_total)&limit={$this->_limit}";

		
			$params = [
	            'access_token' => $this->_access_token,
	            'appsecret_proof' => $this->_appsecret_proof
	        ];

		
			$data = \app\helpers\InsightsHelper::getData($end_point,$params);

			if ($data) {
				$data = $data['data'];
				// if there content
				$where =[
					'type_content_id' => $typeContent->id,
					'resource_id'     => $resource->id,
				];

				for ($d=0; $d < sizeof($data) ; $d++) { 
					$where['content_id'] = \app\helpers\FacebookHelper::getIdPostFacebook($data[$d]['id']);
					$properties = [
						'message'   => $data[$d]['message'],
						'permalink' => $data[$d]['permalink_url'],
						'image_url' => $data[$d]['picture'],
						'timespan'  => \app\helpers\DateHelper::asTimestamp($data[$d]['updated_time']),
					];
					$content = \app\helpers\InsightsHelper::saveContent($where,$properties);
					if (isset($content->id)) {
						
						$this->_postIds['facebook'][] = $content->content_id;

						$model = $data[$d]['insights']['data'];
						\app\helpers\InsightsHelper::saveInsightsFacebookPost($model,$content->id);


						$attachments = $data[$d]['attachments']['data'];
						\app\helpers\InsightsHelper::saveAttachments($attachments,$content->id);
					}// end if isset
				}

			}
			
		}// end if is_null
	}
	/**
	 * [call api to get insights of the page]
	 * @return [array]       [page insigth]
	 */
	public function getInsightsPageInstagram()
	{
		$this->_business_id_instagram = \app\helpers\FacebookHelper::getBusinessAccountId($this->_access_token);

		$end_point = "{$this->_business_id_instagram}/?fields=username,followers_count,profile_picture_url,biography,insights.metric(impressions,reach,follower_count,profile_views).period(day)";

		$params = [
            'access_token' => $this->_access_token,
            'appsecret_proof' => $this->_appsecret_proof
        ];


		return \app\helpers\InsightsHelper::getData($end_point,$params);
	}
	/**
	 * [set the conten in db and his insigth]
	 * @param [array]       [page insigth]
	 */
	public function setInsightsPageInstagram($page)
	{
		$typeContent = \app\models\WTypeContent::find()->select('id')->where(['name' => 'Page'])->one();
		$resource    = \app\models\Resources::find()->select('id')->where(['name' => 'Instagram Comments'])->one();
		$pageId      = (\yii\helpers\ArrayHelper::keyExists('id',$page))? $page['id'] : null;

		if (!is_null($typeContent) && !is_null($resource) && !is_null($pageId)) {
			$where =[
				'type_content_id' => $typeContent->id,
				'resource_id'     => $resource->id,
				'content_id'      => $pageId,
			];

			$properties = [
				'message'   => $page['biography'],
				'permalink' => "https://www.instagram.com/{$page['username']}/",
				'image_url' => $page['profile_picture_url'],
				'timespan'  => \app\helpers\DateHelper::getToday(),
			];

			$content = \app\helpers\InsightsHelper::saveContent($where,$properties);

			if ($content) {
				$insights = $page['insights']['data'];
				if (!empty($page['followers_count'])) {
					$name = 'followers_count';
					$period = 'lifetime';
					$title = 'Usuarios que siguen la página';
					$description = 'El número de usuarios que siguen a la página.';
					$value = $page['followers_count'];
					$metric = \app\helpers\InsightsHelper::setMetric($name,$period,$value,$title,$description);
					array_push($insights, $metric);
				}
				\app\helpers\InsightsHelper::saveInsightsPage($insights,$content->id);
				
			}

		}
	}
	/**
	 * [set the conten in db and his insigth]
	 */
	public function setInsightsPostInstagram()
	{
		$typeContent = \app\models\WTypeContent::find()->select('id')->where(['name' => 'Post'])->one();
		$resource    = \app\models\Resources::find()->select('id')->where(['name' => 'Instagram Comments'])->one();

		if (!is_null($typeContent) && !is_null($resource)) {
			$end_point = "{$this->_business_id_instagram}/media?fields=id,timestamp,shortcode,media_type,media_url,caption,like_count,permalink,thumbnail_url,username,comments_count,insights.metric(impressions,reach,engagement)&limit={$this->_limit}";

		
			$params = [
	            'access_token' => $this->_access_token,
	            'appsecret_proof' => $this->_appsecret_proof
	        ];

			$data = \app\helpers\InsightsHelper::getData($end_point,$params);
			
			if ($data) {
				$data = $data['data'];
				// if there content
				$where =[
					'type_content_id' => $typeContent->id,
					'resource_id'     => $resource->id,
				];

				for ($d=0; $d < sizeof($data) ; $d++) { 
					$where['content_id'] = $data[$d]['id'];
					$properties = [
						'message'   => $data[$d]['caption'],
						'permalink' => $data[$d]['permalink'],
						'image_url' => $data[$d]['media_url'],
						'timespan'  => \app\helpers\DateHelper::asTimestamp($data[$d]['timestamp']),
					];

					$content = \app\helpers\InsightsHelper::saveContent($where,$properties);

					if ($content) {

						$this->_postIds['instagram'][] = $content->content_id;

						$like_count = $data[$d]['like_count'];
						$comments_count = $data[$d]['comments_count'];

						// set new metric
						$like_count_metric = \app\helpers\InsightsHelper::setMetric('likes','lifetime',$like_count,'likes','number of likes the post has');
						$comments_count_metric = \app\helpers\InsightsHelper::setMetric('coments','lifetime',$comments_count,'coments','number of coments the post has');
					
						// get insights
						$insights = $data[$d]['insights']['data'];
						array_push($insights, $like_count_metric, $comments_count_metric);
						\app\helpers\InsightsHelper::saveInsightsInstagramPost($insights,$content->id);
						
					}

				}
			}
		}
	}
	/**
	 * [set the conten in db and his insigth]
	 */
	public function setStorysPostInstagram()
	{
		$typeContent = \app\models\WTypeContent::find()->select('id')->where(['name' => 'Story'])->one();
		$resource    = \app\models\Resources::find()->select('id')->where(['name' => 'Instagram Comments'])->one();

		if (!is_null($typeContent) && !is_null($resource)) {
			$end_point = "{$this->_business_id_instagram}/stories?fields=caption,id,comments_count,media_type,media_url,permalink,timestamp,insights.metric(impressions,reach,replies)";

		
			$params = [
	            'access_token' => $this->_access_token,
	            'appsecret_proof' => $this->_appsecret_proof
	        ];
		
			$data = \app\helpers\InsightsHelper::getData($end_point,$params);

			if (!is_null($data)) {
				$data = $data['data'];
				// if there content
				$where =[
					'type_content_id' => $typeContent->id,
					'resource_id'     => $resource->id,
				];

				for ($d=0; $d < sizeof($data) ; $d++) { 
					$where['content_id'] = $data[$d]['id'];
					$properties = [
						'permalink' => $data[$d]['permalink'],
						'image_url' => $data[$d]['media_url'],
						'timespan'  => \app\helpers\DateHelper::asTimestamp($data[$d]['timestamp']),
					];

					$content = \app\helpers\InsightsHelper::saveContent($where,$properties);

					if ($content) {
						$insights = $data[$d]['insights']['data'];
						if (!empty($insights)) {
							$comments_count = $data[$d]['comments_count'];

							// set new metric
							$comments_count_metric = \app\helpers\InsightsHelper::setMetric('coments','lifetime',$comments_count,'coments','number of coments the post has');
						
							// get insights
							$insights = $data[$d]['insights']['data'];
							array_push($insights,$comments_count_metric);
							\app\helpers\InsightsHelper::saveInsightsInstagramPost($insights,$content->id);
						}
					}
				}
			}
		}
	}

	/**
	 * [setInsightsPostonDbSave call to entitys in the db and call api graphs to insights]
	 */
	public function setInsightsPostonDbSave()
	{
		/*$ids = [
			'facebook' => [
				'10158133637782248',
				'10158129786947248',
				'10158126043867248',
			],
			'instagram' => [
				'17872522222642928',
				'17853415558938642',
				'17855695132882490',
			]
		];*/
		$ids = $this->_postIds;


		$typeContent = \app\models\WTypeContent::find()->select('id')->where(['name' => 'Post'])->one();
		$facebookPostsIds = \yii\helpers\ArrayHelper::getValue($ids,'facebook');
		// if there content
		$where =[
			'type_content_id' => $typeContent->id,
		];
		$params = [
            'access_token' => $this->_access_token,
            'appsecret_proof' => $this->_appsecret_proof
        ];


		if (!empty($facebookPostsIds)) {
			$resource    = \app\models\Resources::find()->select('id')->where(['name' => 'Facebook Comments'])->one();
			$where['resource_id'] = $resource->id;
			

			$facebookPostsIdsDB = \app\models\WContent::find()->select('content_id')->where(
				$where
			)->andWhere(
				[
					'not in',
					'content_id',
					$facebookPostsIds
				]
			)->all();

			
	        if (!empty($facebookPostsIdsDB)) {
	        	$posts = [];

				foreach ($facebookPostsIdsDB as $facebookPost) {
					$end_point = "{$this->_business_id}_{$facebookPost->content_id}?fields=id,permalink_url,updated_time,message,picture,attachments{media,media_type,subattachments,title},insights.metric(post_impressions,post_engaged_users,post_reactions_by_type_total,page_actions_post_reactions_total)";

					$data = \app\helpers\InsightsHelper::getData($end_point,$params);
					if ($data) {
						$posts[] = $data;
					}
				}

			
				for ($p=0; $p < sizeof($posts) ; $p++) { 
					$where['content_id'] = \app\helpers\FacebookHelper::getIdPostFacebook($posts[$p]['id']);
					$properties = [
						'message'   => $posts[$p]['message'],
						'permalink' => $posts[$p]['permalink_url'],
						'image_url' => $posts[$p]['picture'],
						'timespan'  => \app\helpers\DateHelper::asTimestamp($posts[$p]['updated_time']),
					];
					$content = \app\helpers\InsightsHelper::saveContent($where,$properties);
					if (isset($content->id)) {

						$model = $posts[$p]['insights']['data'];
						\app\helpers\InsightsHelper::saveInsightsFacebookPost($model,$content->id);


						$attachments = $posts[$p]['attachments']['data'];
						\app\helpers\InsightsHelper::saveAttachments($attachments,$content->id);
					}// end if isset
					//delete to use var where on query instagram
					unset($where['content_id']);
				}
	        }// end if to facebook on db
		} // end if facebook

		$instagramPostsIds = \yii\helpers\ArrayHelper::getValue($ids,'instagram');

		if (!empty($instagramPostsIds)) {
			$resource    = \app\models\Resources::find()->select('id')->where(['name' => 'Instagram Comments'])->one();
			$where['resource_id'] = $resource->id;

			$instagramPostsIdsDB = \app\models\WContent::find()->select('content_id')->where(
				$where
			)->andWhere(
				[
					'not in',
					'content_id',
					$instagramPostsIds
				]
			)->all();

			$posts = [];
			foreach ($instagramPostsIdsDB as $instagramPost) {
				$end_point = "{$instagramPost->content_id}?fields=ig_id,timestamp,shortcode,media_type,media_url,caption,like_count,permalink,thumbnail_url,username,comments_count,insights.metric(impressions,reach,engagement)";
				
				$data = \app\helpers\InsightsHelper::getData($end_point,$params);
				if ($data) {
					$posts[] = $data;
				}
			}

			for ($p=0; $p < sizeof($posts); $p++) { 

				$where['content_id'] = $posts[$p]['ig_id'];
				$properties = [
					'message'   => $posts[$p]['caption'],
					'permalink' => $posts[$p]['permalink'],
					'image_url' => $posts[$p]['media_url'],
					'timespan'  => \app\helpers\DateHelper::asTimestamp($posts[$p]['timestamp']),
				];


				$content = \app\helpers\InsightsHelper::saveContent($where,$properties);

				if ($content) {

					$like_count = $posts[$p]['like_count'];
					$comments_count = $posts[$p]['comments_count'];

					// set new metric
					$like_count_metric = \app\helpers\InsightsHelper::setMetric('likes','lifetime',$like_count,'likes','number of likes the post has');
					$comments_count_metric = \app\helpers\InsightsHelper::setMetric('coments','lifetime',$comments_count,'coments','number of coments the post has');
				
					// get insights
					$insights = $posts[$p]['insights']['data'];
					array_push($insights, $like_count_metric, $comments_count_metric);
					\app\helpers\InsightsHelper::saveInsightsInstagramPost($insights,$content->id);
					
				}
			}
		}

	}
	
}