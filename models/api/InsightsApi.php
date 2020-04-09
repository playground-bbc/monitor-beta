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
	private $_business_name;
	private $_business_id;
	
	private $_appsecret_proof;
	private $_limit = 5;
	
	private $_baseUrl = 'https://graph.facebook.com/v6.0';
	

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

	public function getInsightsPageFacebook()
	{
		
		$today  = \app\helpers\DateHelper::getToday();
		
		$end_point = "{$this->_business_id}?fields=id,link,about,engagement,picture{url},insights.metric(page_impressions,page_impressions_unique,page_post_engagements).since({$today}).until({$today}).period(day)";
		
		$params = [
            'access_token' => $this->_access_token,
            'appsecret_proof' => $this->_appsecret_proof
        ];


		$data = null;
		$client = new yii\httpclient\Client(['baseUrl' => $this->_baseUrl]);
		
		try {
			
			$accounts = $client->get($end_point,$params)->send();
			if ($accounts->isOk) {
        		$data = $accounts->getData();
			}
        	
        	if(isset($data['error'])){
        		// to $user_credential->user->username and $user_credential->name_app
        		// error send email with $data['error']['message']
        		$data = null;
        	}
			
		} catch (\yii\httpclient\Exception $e) {
			// send email
		}
		return (!is_null($data))? $data : false;
	}


	public function setInsightsPageFacebook($page)
	{
		$typeContent = \app\models\WTypeContent::find()->select('id')->where(['name' => 'Page'])->one();
		$resource    = \app\models\Resources::find()->select('id')->where(['name' => 'Facebook Comments'])->one();

		$pageId = $page['id'];	
		if (!is_null($typeContent) && !is_null($resource)) {
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
				$data = $page['insights']['data'];
				$end_time = \app\helpers\DateHelper::asTimestamp($data[0]['values'][0]['end_time']);

				$is_insights =  \app\models\WInsights::find()->where(
					[
						'end_time'   => $end_time,
						'content_id' => $content->id,
					]
				)->exists();

				if (!$is_insights) {
					for ($d=0; $d < sizeof($data) ; $d++) { 
						$insights = new \app\models\WInsights();
						$insights->content_id = $content->id;
						$insights->name = $data[$d]['name'];
						$insights->title = $data[$d]['title'];
						$insights->description = $data[$d]['description'];
						$insights->insights_id = $data[$d]['id'];
						$insights->period = $data[$d]['period'];
						for ($v=0; $v < sizeof($data[$d]['values']) ; $v++) { 
							$insights->value = $data[$d]['values'][$v]['value'];
							$insights->end_time = \app\helpers\DateHelper::asTimestamp($data[$d]['values'][$v]['end_time']);
						}

						if(!$insights->save()){
							var_dump($insights->errors);
						}
					}
				}

				
			}

		}
	}

	public function setInsightsPostFacebook($page)
	{
		$typeContent = \app\models\WTypeContent::find()->select('id')->where(['name' => 'Post'])->one();
		$resource    = \app\models\Resources::find()->select('id')->where(['name' => 'Facebook Comments'])->one();

		$pageId = $page['id'];

		if (!is_null($typeContent) && !is_null($resource)) {
			
			$end_point = "{$this->_business_id}/published_posts?fields=id,permalink_url,updated_time,message,picture,attachments{media,media_type,subattachments,title},insights.metric(post_impressions,post_engaged_users,post_reactions_by_type_total,page_actions_post_reactions_total)&limit={$this->_limit}";

		
			$params = [
	            'access_token' => $this->_access_token,
	            'appsecret_proof' => $this->_appsecret_proof
	        ];

	        
	        $data = null;
	        $client = new yii\httpclient\Client(['baseUrl' => $this->_baseUrl]);
		
			try {
				
				$posts = $client->get($end_point,$params)->send();
				if ($posts->isOk) {
					$data = $posts->getData();
				}
	        	

	        	
	        	if(isset($data['error'])){
	        		// to $user_credential->user->username and $user_credential->name_app
	        		// error send email with $data['error']['message']
	        		$data = null;
	        	}
				
			} catch (\yii\httpclient\Exception $e) {
				// send email
			}

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
						'message'   => $data[$d]['message'],
						'permalink' => $data[$d]['permalink_url'],
						'image_url' => $data[$d]['picture'],
						'timespan'  => \app\helpers\DateHelper::asTimestamp($data[$d]['updated_time']),
					];
					$content = \app\helpers\InsightsHelper::saveContent($where,$properties);
					if (isset($content->id)) {
						$model = $data[$d]['insights']['data'];
						\app\helpers\InsightsHelper::saveInsights($model,$content->id);


						$attachments = $data[$d]['attachments']['data'];
						\app\helpers\InsightsHelper::saveAttachments($attachments,$content->id);
					}// end if isset
				}

			}
			
		}// end if is_null
	}
	
}