<?php 
namespace app\models\api;

use Yii;
use yii\base\Model;
use yii\helpers\ArrayHelper;
use yii\helpers\Console;
use yii\httpclient\Client;

use app\models\file\JsonFile;


/**
 *
 * @author Eduardo Morales <eduardo@montana-studio.com>
 * @group  Montana-Studio LG 
 */

/**
 * InstagramCommentsApi is the model behind the login API.
 *
 */
class InstagramCommentsApi extends Model {
	
	public $userId;
	public $alertId;
	public $end_date;
	public $start_date;
	public $resourcesId;
	public $products;
	
	public $data;



	private $_baseUrl = 'https://graph.facebook.com/v4.0';
	
	private $_limit_post = 1;
	private $_limit_commets = 25;
	
	//private $_access_secret_token;
	
	private $_page_access_token;
	private $_business_account_id;
	private $_appsecret_proof;

	private $_client;



	
	/**
	 * [prepare params for the query]
	 * @param  [array] $alert [current alert]
	 * @return [array]        [array params]
	 */
	public function prepare($alert){
		
		if(!empty($alert)){
			// set variables
			$this->alertId    = $alert['id'];
			$this->userId     = $alert['userId'];
			$this->start_date = $alert['config']['start_date'];
			$this->end_date   = $alert['config']['end_date'];

			
			// order products by his  length
			array_multisort(array_map('strlen', $alert['products']), $alert['products']);
			$this->products   = $alert['products'];
			if (count($this->products)) {
				return $this->_setParams();
			} else {
				return false;
			}
		}
		return false;
	}

	/**
	 * [call loop in to for each alert and call method _getComments]
	 * @param  array  $query_params   [array of query]
	 * @return [type]                  [data]
	 */
	public function call($query_params = []){

		
		//$this->data[] = $this->_getDataApi($query_params);
		$data = $this->_getDataApi($query_params);
		// set if search finish
		//$this->searchFinish();

		if($data){
			$this->data[] = $data;
		}
		
	}
	/**
	 * [_getDataApi description]
	 * @param  [type] $query_params [description]
	 * @return [type]               [description]
	 */
	private function _getDataApi($query_params){


		$feeds = $this->_getPosts($query_params);
		
		// if there post
		if(count($feeds)){
			$filter_feeds = $this->_filterFeedsbyProducts($feeds);
			
			$feeds_comments = $this->_getComments($filter_feeds);
			$feeds_comments_replies = $this->_getReplies($feeds_comments);
			$model = $this->_orderFeedsComments($feeds_comments_replies);
			
			return $model;

		}
			
	}

	/**
	 * [_getPosts get post instagram_business_account]
	 * @param  [array] $query_params [description]
	 * @return [array]               [feeds]
	 */
	private function _getPosts($query_params){
		$client = $this->_client;
		// simple query
		if(\yii\helpers\ArrayHelper::keyExists('query', $query_params, false) ){

			$after = '';
			$index = 0;
			$responseData = [];
			// lets loop if next in post or comments and there limit facebook	
			do {
				
				try{
					
					$posts = $client->get($query_params['query'],[
						'after' => $after,
						'access_token' => $this->_page_access_token,
						'appsecret_proof' => $this->_appsecret_proof
					])
					->setOptions([
			        'timeout' => 15, // set timeout to 5 seconds for the case server is not responding
			    	])->send();

					
					$responseHeaders = $posts->headers->get('x-business-use-case-usage'); // get headers


					// if get error data
					if(\yii\helpers\ArrayHelper::getValue($posts->getData(),'error' ,false)){
						$error = \yii\helpers\ArrayHelper::getValue($posts->getData(),'error' ,false);
						var_dump($error);
						// send email with data $responseData[$index]['error']['message']
						break;
					}

					// is over the limit
					if(\app\helpers\FacebookHelper::isCaseUsage($responseHeaders,$this->_business_account_id)){
						break;
					}
					
					// get the after
					if(\yii\helpers\ArrayHelper::getValue($posts->getData(),'paging.next' ,false)){ // if next
						$after = \yii\helpers\ArrayHelper::getValue($posts->getData(),'paging.cursors.after' ,false);
						$is_next = true;
					}else{
						$is_next = false;
					} 

					$data =  $posts->getData(); // get all post and comments


					if(isset($data['data'][0]['timestamp'])){
						
						$date_post = $data['data'][0]['timestamp'];
						$end_date = strtotime(\app\helpers\DateHelper::add($this->end_date,'+1 day'));

						if(\app\helpers\DateHelper::isBetweenDate($date_post,$this->start_date,$end_date)){
							$responseData[$index] = $data;
							$index++;
						}
						$date_post_unix = strtotime($data['data'][0]['timestamp']);

						if(\app\helpers\FacebookHelper::isPublicationNew($this->start_date,$date_post_unix)){
							$between = true;
						}else{
							$between = false;
						}

					}else{
						echo "is break";
						break;
					}
					
					// test
					if(isset($data['data'][0]['comments']['data'])){
						$responseData[$index] = $data;
						$index++;
					}
					
					
					

				}catch(\yii\httpclient\Exception $e){
					// send a email with no internet connection
					 echo 'Excepción capturada: ',  $e->getMessage(), "\n";
					 die();
				}

			
			}while($is_next && $between);
		
			return $responseData;
		}
	}

	/**
	 * [_filterFeedsbyProducts filter feeds by products]
	 * @param  [array] $feeds [data feeds]
	 * @return [array] $feeds [feed filter]
	 */
	private function _filterFeedsbyProducts($feeds){
		$posts = [];
		$feed_count = count($feeds);

		// params to save in AlertMentionsHelper and get
		$where = [
			//'condition'   => 'ACTIVE',
			'type'        => 'comments Instagram',
			'alertId'     => $this->alertId,
			'resourcesId' => $this->resourcesId,
		];
		

		for($f = 0; $f < count($feeds);$f++){
			if(isset($feeds[$f]['data'])){
				for($d = 0; $d < count($feeds[$f]['data']); $d++){
					
					$feedId       = $feeds[$f]['data'][$d]['id'];
					$caption      = $feeds[$f]['data'][$d]['caption'];
					$url          = $feeds[$f]['data'][$d]['permalink'];
					$like_count   = $feeds[$f]['data'][$d]['like_count'];


					$timestamp = \app\helpers\DateHelper::asTimestamp($feeds[$f]['data'][$d]['timestamp']);
					
					for($p = 0; $p < sizeof($this->products); $p++){
						// destrutura el product
						$product_data = \app\helpers\StringHelper::structure_product_to_search($this->products[$p]);
						// $is_contains = (count($product_data) > 3) ? \app\helpers\StringHelper::containsAny($caption,$product_data) : \app\helpers\StringHelper::containsAll($caption,$product_data);
						$is_contains =  \app\helpers\StringHelper::containsAny($caption,$product_data);
						if($is_contains){
							if($feed_count){
								// if a not key
								if(!ArrayHelper::keyExists($this->products[$p], $posts, false)){
									$posts [$this->products[$p]] = [] ;

								}
								// if not value
								if(!in_array($feeds[$f]['data'][$d],$posts[$this->products[$p]])){
									$where['publication_id'] = $feedId;
									$mention_data['like_count'] = $like_count;
									if(!\app\helpers\AlertMentionsHelper::isAlertsMencionsExists($feedId,$this->alertId)){

										
									
										\app\helpers\AlertMentionsHelper::saveAlertsMencions($where,['term_searched' => $this->products[$p],'date_searched' => $timestamp,'title' => $caption,'url' => $url,'mention_data' => $mention_data]);

										//$posts[$this->products[$p]][] = $feeds[$f]['data'][$d];
										$feed_count--;
										break;

									}else{
										\app\helpers\AlertMentionsHelper::saveAlertsMencions($where,['mention_data' => $mention_data]);
									}
									$posts[$this->products[$p]][] = $feeds[$f]['data'][$d];
								} // end if !in_array
							} // end feed_count
						}// end if is_contains
					}// end loop products
				}// end loop data
			}//end if isset()
		}// end loop

		return $posts;

	}
	/**
	 * [_getComments get comments from post]
	 * @param  [array] $feeds [description]
	 * @return [array]        [description]
	 */
	private function _getComments($feeds){

		$client = $this->_client;

		// params to save in AlertMentionsHelper and get
		$where = [
			'condition'   => 'ACTIVE',
			'type'        => 'comments Instagram',
			'alertId'     => $this->alertId,
			'resourcesId' => $this->resourcesId,
		];

		$query = \app\helpers\AlertMentionsHelper::getAlersMentions($where);
		if($query){
			$params['feeds'] = ArrayHelper::index($query,'publication_id');
		}
		
		foreach ($feeds as $product => $feed){
			for($f =  0; $f < sizeof($feed); $f++){
				
				$id_feed = $feed[$f]['id'];
				$comments_count = (int) $feed[$f]['comments_count'];

				$model_post = \app\models\AlertsMencions::find()->where($where)->andWhere(['publication_id' => $id_feed])->one();

				if($comments_count > count($model_post->mentions)){
					$timestamp = \app\helpers\DateHelper::asTimestamp($feed[$f]['timestamp']);
					// if there next in the database
					if(isset($params)){
						if (ArrayHelper::keyExists($id_feed, $params['feeds'], false)) {
							if($params['feeds'][$id_feed]['next'] != ''){
								$next = $params['feeds'][$id_feed]['next'];
								// clean next in the database
								$where['publication_id'] = $id_feed;
								\app\helpers\AlertMentionsHelper::saveAlertsMencions($where,['next' => null]);
							}
						} //end if keyExists
					}// if isset params
					
					
					$after = '';
					$data = [];
					$flag = false;

					do{
						$query = $this->_commentSimpleQuery($id_feed);

						$comments = $client->createRequest()
							->setMethod('GET')
							->setUrl("{$this->_baseUrl}/{$query}")
							->setData([
								'after' => $after,
								'appsecret_proof' => $this->_appsecret_proof
							])
							->setOptions([
								'timeout' => 15, // set timeout to 5 seconds for the case server is not responding
							])
							->send();


						$responseHeaders = $comments->headers->get('x-business-use-case-usage'); // get headers
						// set comments
						
						

						// if get  data
						if(\yii\helpers\ArrayHelper::keyExists('data',$comments->getData() ,false)){
							// get comments
							$tmp = $comments->getData();
							for($t = 0; $t < sizeof($tmp['data']); $t++){
								$data[] = $tmp['data'][$t];
							}
						}

						// if get error data
						if(\yii\helpers\ArrayHelper::keyExists('error',$comments->getData(),false)){
							// send email with data $responseData[$index]['error']['message']
							break;
						}
						
						// get the after
						if(\yii\helpers\ArrayHelper::getValue($comments->getData(),'paging.next' ,false)){ // if next
							$after_url = \yii\helpers\ArrayHelper::getValue($comments->getData(),'paging.next' ,false);
							$after = \app\helpers\StringHelper::parses_url($after_url,'after');
							$is_next = true;
						}else{
							$after = '';
							$is_next = false;

						}
						// is over the limit
						$is_usage_limit = \app\helpers\FacebookHelper::isCaseUsage($responseHeaders,$this->_business_account_id);
						
						if($is_usage_limit){
							// save the next 
							if($next){
								$where['publication_id'] = $id_feed;
							//  Console::stdout("save one time {$next}.. \n", Console::BOLD);
								$model_alert = \app\helpers\AlertMentionsHelper::saveAlertsMencions($where,['next' => $next]);
							}
						}
						

					}while($is_next);

					// looking new comments
					if(\yii\helpers\ArrayHelper::keyExists($id_feed,$params['feeds'])){

						$model = \app\models\AlertsMencions::findOne(['publication_id' => $id_feed]);
						if(!$params['feeds'][$id_feed]['max_id']){
							$firts_comment = reset($data);
							if($firts_comment){
								$model->max_id = \app\helpers\DateHelper::asTimestamp($firts_comment['timestamp']);
								if($model->save()){
									$feeds[$product][$f]['comments'] = $data;
								} // if save and if not send email with fail
							}// if there firts_comment
						}else{// if not max_id records
							$max_id = $params['feeds'][$id_feed]['max_id'];

							for($d = 0; $d < sizeof($data); $d++){
								$unix_date = \app\helpers\DateHelper::asTimestamp($data[$d]['timestamp']);
								$feeds[$product][$f]['comments'][] = $data[$d];
								// coment by update likes in comments
								/*if($unix_date > $max_id){
									$model->max_id = $unix_date;
									if($model->save()){
										$feeds[$product][$f]['comments'][] = $data[$d];
									}
								}*/
							}
						}// if max_id	
					}// if old records
				}
			}// end loop feed
		}// end foreach feeds

		return $feeds;

	}
	/**
	 * [_getReplies get replies the comments]
	 * @param  [array] $feeds [description]
	 * @return [array]        [feeds with comments and replies]
	 */
	private function _getReplies($feeds){
		$client = $this->_client;

		foreach ($feeds as $product => $feed){
			for($f =  0; $f < sizeof($feed); $f++){
				if(ArrayHelper::keyExists('comments', $feed[$f], false)){
					for($c = 0; $c < sizeof($feed[$f]['comments']); $c++){
						$comentId = $feed[$f]['comments'][$c]['id'];
						$query = $this->_repliesSimpleQuery($comentId);

						$replies = $client->createRequest()
					    ->setMethod('GET')
					    ->setUrl($query)
					    ->setData([
					    	'appsecret_proof' => $this->_appsecret_proof
						])
						->setOptions([
							'timeout' => 15, // set timeout to 5 seconds for the case server is not responding
						])
					    ->send();


			    		$responseHeaders = $replies->headers->get('x-business-use-case-usage'); // get headers

			    		// if get  data
						if(\yii\helpers\ArrayHelper::keyExists('data',$replies->getData() ,false)){
							// get comments
							$feeds[$product][$f]['comments'][$c]['replies'] = $replies->getData();
						}// end if keyExists

						// if get error data
						if(\yii\helpers\ArrayHelper::keyExists('error',$replies->getData(),false)){
							// send email with data $responseData[$index]['error']['message']
							break;
						}
						// is over the limit
                    	$is_usage_limit = \app\helpers\FacebookHelper::isCaseUsage($responseHeaders,$this->_business_account_id);
                    	if($is_usage_limit){
							// send email with is_usage_limit
							break;
						}

					} // end for comments
				}// end if comments key
			}// end loop feed	
		}// end foreach	

		return $feeds;	
	}

	private function _orderFeedsComments($feeds){
		$model = [];

		foreach($feeds as $product => $posts){
			for($p =  0; $p < sizeof($posts); $p++){
				if(!ArrayHelper::keyExists($product, $model, false)){
					$model[$product] = [];
				} // end if keyExists
				if(!ArrayHelper::keyExists('comments', $posts[$p], false) ){
					if(!in_array($posts[$p],$model[$product])){
						$model[$product][] = $posts[$p];
					}// en if in array
					
				}else{
					$comments = ArrayHelper::remove($posts[$p],'comments');
					$posts[$p]['comments'] = [];
					if(!in_array($posts[$p],$model[$product])){
						$model[$product][] = $posts[$p];
						for($c = 0; $c <  sizeof($comments); $c++){

							$tmp = $comments[$c];
							$tmp['message_markup'] = $comments[$c]['text'];
							if(ArrayHelper::keyExists('replies', $comments[$c], false)){
								if(count($comments[$c]['replies']['data'])){
									for($r = 0; $r < sizeof($comments[$c]['replies']['data']);$r++){
										$tmp['replies']['data'][$r]['message_markup'] = $comments[$c]['replies']['data'][$r]['text'];
									}//end for replies
								}// end if !count
							}// end if array keyExists
							if(!in_array($comments[$c],$model[$product][$p]['comments'])){
								$model[$product][$p]['comments'][] = $tmp;
							}// end if in array
						}
					}// en if in array

				}// end if keyExists comments
			}// loop posts
		} // forearch
		return $model;
	}
	/**
	 * [_setParams set params to build the call]
	 */
	private function _setParams(){

		$params = [];
		// get the user credentials
		$user_credential = \app\helpers\FacebookHelper::getCredencials($this->userId);
		// get page token   
		$this->_page_access_token = \app\helpers\FacebookHelper::getPageAccessToken($user_credential);
		// get busines id
		$this->_business_account_id = \app\helpers\FacebookHelper::getBusinessAccountId($user_credential->access_secret_token);
		// get app_proof
		$this->_appsecret_proof = \app\helpers\FacebookHelper::getAppsecretProof($this->_page_access_token);
		// loading firts query
		$params['query'] = $this->_postSimpleQuery();  

		return $params; 

	}

	/**
	 * [_postSimpleQuery description]
	 * @return [type] [description]
	 */
	private function _postSimpleQuery(){		

		$post_query = "{$this->_business_account_id}/media?fields=timestamp,caption,like_count,permalink,thumbnail_url,username,comments_count&limit={$this->_limit_post}";

		return $post_query;

	}


	/**
	 * [_postSimpleQuery description]
	 * @return [type] [description]
	 */
	private function _commentSimpleQuery($feedId){

		/*$comments_query = "{$feedId}?fields=comments.limit({$this->_limit_commets}){user,username,timestamp,text,like_count,id,replies.limit($this->_limit_commets){username,text,timestamp,hidden}}";*/
		$comments_query = "{$feedId}/comments?access_token={$this->_page_access_token}&fields=user%2Cusername%2Ctimestamp%2Ctext%2Clike_count%2Cid&limit={$this->_limit_commets}";

		return $comments_query;

	}

	private function _repliesSimpleQuery($commentId){
		return "{$commentId}/replies?fields=username,timestamp,text,id,like_count&access_token={$this->_page_access_token}";
	}



	/**
	 * [saveJsonFile save a json file]
	 * @return [none] [description]
	 */
	public function saveJsonFile(){
		$source = 'Instagram Comments';
		if(!is_null($this->data)){
			foreach ($this->data as $data){
				foreach($data as $product => $feed){
					$jsonfile = new JsonFile($this->alertId,$source);
					$jsonfile->load($data);
				}
				$jsonfile->save();
			}
		}

	}

	private function searchFinish()
	{
		$model = [
            'Instagram Comments' => [
                'resourceId' => $this->resourcesId,
                'status' => 'Pending'
            ]
        ];

        $today = \app\helpers\DateHelper::getToday();
        $end_date = strtotime(\app\helpers\DateHelper::add($this->end_date,'1 day'));

        if($today >= $end_date){
        	$alermentions = \app\models\AlertsMencions::find()->where([
        		'alertId' => $this->alertId,
        		'resourcesId' => $this->resourcesId,
        		'type' => 'comments Instagram'
        	])->all();
        	
        	if (count($alermentions)) {
        		foreach ($alermentions as $alermention) {
	        		$alermention->condition = 'INACTIVE';
	        		$alermention->save();
	        	}
        	}
        	$model['Instagram Comments']['status'] = 'Finish'; 
        }

        \app\helpers\HistorySearchHelper::createOrUpdate($this->alertId, $model);

	}

	/**
	 * [_getBusinessAccountId get bussinessId]
	 * @param  [type] $user_credential [description]
	 * @return [string]                  [description]
	 */
	private function _getBusinessAccountId($user_credential){
		
		$bussinessId = Yii::$app->params['facebook']['business_id'];
		$appsecret_proof = \app\helpers\FacebookHelper::getAppsecretProof($user_credential->access_secret_token);

		$params = [
            'access_token' => $user_credential->access_secret_token,
            'appsecret_proof' => $appsecret_proof
        ];

        $BusinessAccountId = null;
       
        try{
        	
        	$accounts = $this->_client->get("{$bussinessId}?fields=instagram_business_account",$params)->send();
        	$data = $accounts->getData();
        	if(isset($data['error'])){
        		// to $user_credential->user->username and $user_credential->name_app
        		// error send email with $data['error']['message']
        		return null;
        	}
      
        	$BusinessAccountId = $data['instagram_business_account']['id']; 

        }catch(\yii\httpclient\Exception $e){
        	// problem conections
        	// send a email
        }
        

        return (!is_null($BusinessAccountId)) ? $BusinessAccountId : null;

	}


	/**
	 * [_getClient return client http request]
	 * @return [obj] [return object client]
	 */
	private function _getClient(){
		$this->_client = new Client(['baseUrl' => $this->_baseUrl]);
		// $this->_client = new Client([
		//     // Base URI is used with relative requests
		//     'base_uri' => $this->_baseUrl,
		//     // You can set any number of default request options.
		//     'timeout'  => 2.0,
		// ]);
		return $this->_client;
	}

	/**
	 * [_setResourceId return the id from resource]
	 */
	private function _setResourceId(){
		
		$socialId = (new \yii\db\Query())
		    ->select('id')
		    ->from('type_resources')
		    ->where(['name' => 'Social media'])
		    ->one();
		
		
		$resourcesId = (new \yii\db\Query())
		    ->select('id')
		    ->from('resources')
		    ->where(['name' => 'Instagram Comments','resourcesId' => $socialId['id']])
		    ->all();
		

		$this->resourcesId = ArrayHelper::getColumn($resourcesId,'id')[0];    
	}

	function __construct(){
		
		// set resource 
		$this->_setResourceId();

		// get client
		$this->_getClient();
		
		parent::__construct(); 
	}
}

?>