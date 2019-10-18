<?php
namespace app\models\api;

use Yii;
use yii\base\Model;
use yii\helpers\ArrayHelper;
use yii\helpers\Console;

use yii\httpclient\Client;


/**
 *
 * @author Eduardo Morales <eduardo@montana-studio.com>
 * @group  Montana-Studio LG 
 */

/**
 * FacebookApi is the model behind the login API.
 *
 */
class FacebookCommentsApi extends Model {

	

	public $userId;
	public $alertId;
	public $end_date;
	public $start_date;
	public $resourcesId;
	
	public $data;



	private $_baseUrl = 'https://graph.facebook.com';
	
	private $_limit_post = 1;
	private $_limit_commets = 5;
	
	//private $_access_secret_token;
	
	private $_page_access_token;

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

			 return $this->_setParams();
		}
		return false;
	}

	/**
	 * [_setParams set params to build the call]
	 */
	private function _setParams(){

		$params = [];
		// get the user credentials
		$user_credential = \app\helpers\FacebookHelper::getCredencials($this->userId);
		// get last search in the api if in isset
		/*$query = \app\helpers\AlertMentionsHelper::getAlersMentions([
			'alertId'     => $this->alertId,
			'resourcesId' => $this->resourcesId,
			'condition'   => 'ACTIVE',
			'type'        => 'comments',
		]);*/
		
		/*if(empty($query)){ // there is not  previus search .. well lets find out 
			// get page token   
			$this->_page_access_token = $this->_getPageAccessToken($user_credential->access_secret_token);
			// loading firts query
			$params['query'] = $this->_postCommentsSimpleQuery();

		}else{
			$params['feeds'] = ArrayHelper::index($query,'publication_id');
			$params['query'] = $this->_postCommentsSimpleQuery();
		}*/

		// get page token   
		$this->_page_access_token = $this->_getPageAccessToken($user_credential->access_secret_token);
		// loading firts query
		$params['query'] = $this->_postCommentsSimpleQuery();  

		return $params; 

	}


	/**
	 * [call loop in to for each alert and call method _getComments]
	 * @param  array  $query_params   [array of query]
	 * @return [type]                  [data]
	 */
	public function call($query_params = []){

		
		$this->data[] = $this->_getDataApi($query_params);
		
	}

	private function _getDataApi($query_params){

		$feeds = $this->_getPostsComments($query_params);

		// if not empty post
		if(!empty($feeds[0]['data'])){
			$feeds_comments = $this->_getComments($feeds);
			$feeds_reviews = $this->_getSubComments($feeds_comments);
			//var_dump($feeds_reviews);
		}
		

	}

	private function _getPostsComments($query_params){
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
					])->send();

					
					$responseHeaders = $posts->headers->get('x-business-use-case-usage'); // get headers

					// if get error data
					if(\yii\helpers\ArrayHelper::getValue($posts->getData(),'error' ,false)){
						// send email with data $responseData[$index]['error']['message']
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
					

					if(isset($data['data'][0]['comments']['data'])){
						$responseData[$index] = $data;
						$index++;
					}
					
					// is over the limit
					$is_usage_limit = \app\helpers\FacebookHelper::isCaseUsage($responseHeaders);
					

					

				}catch(\yii\httpclient\Exception $e){
					// send a email with no internet connection
					 echo 'Excepción capturada: ',  $e->getMessage(), "\n";
					 die();
				}

			
			}while($is_next xor $is_usage_limit);
		
			return $responseData;
		}
	}

	private function _getComments($feeds){
		$client = $this->_client;
		 
		// params to save in AlertMentionsHelper and get
		$where = [
			'condition'   => 'ACTIVE',
			'type'        => 'comments',
			'alertId'     => $this->alertId,
			'resourcesId' => $this->resourcesId,
		];

		$query = \app\helpers\AlertMentionsHelper::getAlersMentions($where);
		if($query){
			$params['feeds'] = ArrayHelper::index($query,'publication_id');
		}
		


		// for each pagination
		for($p = 0; $p < sizeOf($feeds); $p++){
			// for each feed is limit is one
			for($d=0; $d < sizeOf($feeds[$p]['data']); $d++){
				// take id post
				$id_feed = $feeds[$p]['data'][$d]['id'];
				// if there comments
				if(isset($feeds[$p]['data'][$d]['comments'])){
					// if there next
					if(isset($feeds[$p]['data'][$d]['comments']['paging']['next'])){
						
						$next = $feeds[$p]['data'][$d]['comments']['paging']['next'];
						/**
						 * TODO TEST
						 */
						// if there next in the database
						if (ArrayHelper::keyExists($id_feed, $params['feeds'], false)) {
							if($params['feeds'][$id_feed]['next'] != ''){
								$next = $params['feeds'][$id_feed]['next'];
								// clean next in the database
								$where['publication_id'] = $id_feed;
								\app\helpers\AlertMentionsHelper::getAlersMentions($where,['next' => null]);
							}
						}
						
						$comments = [];

						do{

							$commentsResponse = $client->get($next)->send();// more comments then

							$comments =  $commentsResponse->getData(); // get all post and comments

							$responseHeaders = $commentsResponse->headers->get('x-business-use-case-usage'); // get headers
							// if get error data
                            if(\yii\helpers\ArrayHelper::getValue($comments,'error' ,false)){
                                // send email with data $responseData[$index]['error']['message']
                                break;
                            }
                            // get the after
                            if(\yii\helpers\ArrayHelper::getValue($comments,'paging.next' ,false)){ // if next
                                $next = \yii\helpers\ArrayHelper::getValue($comments,'paging.next' ,false);
                                $is_next = true;
                            }else{
                                $is_next = false;
                            } 

                            // is over the limit
                            $is_usage_limit = \app\helpers\FacebookHelper::isCaseUsage($responseHeaders);
                            if($is_usage_limit){
								// save the next 
								if($next){
									$where['publication_id'] = $id_feed;
							        Console::stdout("save one time {$next}.. \n", Console::BOLD);
							        $model_alert = \app\helpers\AlertMentionsHelper::saveAlertsMencions($where,['next' => $next]);
								}
							}
                            
                            // if there more comments
                            if(!empty($comments['data'])){
                            	for($n = 0; $n < sizeOf($comments['data']); $n++){
                            		$feeds[$p]['data'][$d]['comments']['data'][] =$comments['data'][$n];
                            	}
                            }

                           // $index++;

						}while($is_next xor $is_usage_limit);


					}
				
					// search for max_id
					if(ArrayHelper::keyExists($id_feed, $params['feeds'], false)){
						
						$func = function($valor,$max_id){
							var_dump($max_id);
							die();
						    $comments = [];
						    $lasted_update = $params['max_id'];
						    for($v=0; $v < count($valor['data']); $v++){
						    	$unix_time = \app\helpers\DateHelper::asTimestamp($valor['data'][$v]['created_time']);
						    	if(\app\helpers\FacebookHelper::isPublicationNew($lasted_update,$unix_time)){
						    		echo 'is new';
						    	}
						    }
						    //return $valor * 2;
						};
						$max_id = $params['feeds'][$id_feed]['max_id'];
						$feeds[$p]['data'][$d]['comments']['data'] = array_map($func, $feeds[$p]['data'][$d]['comments'],[$max_id]);
					}
					
				}
			}
			
		}

		return $feeds;
		
	}

	private function _getSubComments($feeds_comments){
		$client = $this->_client;

		// params to save in AlertMentionsHelper and get
		$where = [
			'condition'   => 'ACTIVE',
			'type'        => 'comments',
			'alertId'     => $this->alertId,
			'resourcesId' => $this->resourcesId,
		];

		$query = \app\helpers\AlertMentionsHelper::getAlersMentions($where);
		if($query){
			$params['feeds'] = ArrayHelper::index($query,'publication_id');
		}
		


		// for each pagination
		for($p = 0; $p < sizeOf($feeds_comments); $p++){
			// for each data
			for($d=0; $d < sizeOf($feeds_comments[$p]['data']); $d++){

				$lasted_update = $feeds_comments[$p]['data'][$d]['updated_time'];
				$id_feed = $feeds_comments[$p]['data'][$d]['id'];
				

				// if there comments
				if(isset($feeds_comments[$p]['data'][$d]['comments'])){

					// loop in comments
					for($c=0; $c < sizeOf($feeds_comments[$p]['data'][$d]['comments']['data']); $c++){
						// IF THERE SUBCOMMENTS
						if(isset($feeds_comments[$p]['data'][$d]['comments']['data'][$c]['comments'])){
							//echo 'its data..';
							// loop through subcomments
							for($s=0; $s < sizeOf($feeds_comments[$p]['data'][$d]['comments']['data'][$c]['comments']['data']); $s++){
								
								$id_message = $feeds_comments[$p]['data'][$d]['comments']['data'][$c]['comments']['data'][$s]['id'];

								//echo $id_message. "\n";
								
								$commentsResponse = $client->get($id_message,[
									'access_token' => $this->_page_access_token
								])->send();// more comments then
								
								// if get error data
	                            if(\yii\helpers\ArrayHelper::getValue($commentsResponse->getData(),'error' ,false)){
	                                // send email with data $responseData[$index]['error']['message']
	                                break;
	                            }

	                            $responseHeaders = $commentsResponse->headers->get('x-business-use-case-usage'); // get headers
	                            // if over the limit
	                            if(\app\helpers\FacebookHelper::isCaseUsage($responseHeaders)){
	                            	break;
	                            }


								array_push($feeds_comments[$p]['data'][$d]['comments']['data'][$c]['comments']['data'][$s],$commentsResponse->getData());
							}
						}
					}
				}	
				// save lasted_update
				$unix_time = \app\helpers\DateHelper::asTimestamp($lasted_update);
  				\app\helpers\AlertMentionsHelper::saveAlertsMencions($where,['max_id' => $unix_time,'publication_id' => $id_feed]);
			}

		}
		return $feeds_comments;
	}

	


	/**
	 * [_getPageAccessToken get page access token token]
	 * @param  [string] $access_secret_token [description]
	 * @return [string] [PageAccessToken]
	 */
	private function _getPageAccessToken($access_secret_token){
		$params = [
            'access_token' => $access_secret_token
        ];

        $page_access_token = null;
       
        try{
        	
        	$accounts = $this->_client->get('me/accounts',$params)->send();
        	$data = $accounts->getData();
        	$page_access_token = ArrayHelper::getColumn($data['data'],'access_token')[0]; 

        }catch(\yii\httpclient\Exception $e){
        	// problem conections
        	// send a email
        }
        

        return (!is_null($page_access_token)) ? $page_access_token : null;
	}
	/**
	 * [_postCommentsSimpleQuery buidl a simple query post and their comments]
	 * @param  [string] $access_token_page [access_token_page by page]
	 * @return [string] $post_comments_query [query to call]
	 */
	private function _postCommentsSimpleQuery(){

		$bussinessId = Yii::$app->params['facebook']['business_id'];

		$post_comments_query = "{$bussinessId}/posts?fields=from,full_picture,icon,is_popular,message,attachments{unshimmed_url},shares,created_time,comments{from,created_time,like_count,message,parent,comment_count,attachment,comments.limit($this->_limit_commets){likes.limit(10),comments{message}},permalink_url},updated_time&until={$this->end_date}&since={$this->start_date}&limit={$this->_limit_post}";

		return $post_comments_query;
	}

	/**
	 * [_getClient return client http request]
	 * @return [obj] [return object client]
	 */
	private function _getClient(){
		$this->_client = new Client(['baseUrl' => 'https://graph.facebook.com']);
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
		    ->where(['name' => 'Facebook Comments','resourcesId' => $socialId['id']])
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