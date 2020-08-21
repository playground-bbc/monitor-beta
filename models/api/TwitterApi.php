<?php
namespace app\models\api;

use Yii;
use yii\base\Model;
use yii\helpers\ArrayHelper;
use yii\helpers\Console;

use app\helpers\DateHelper;

use app\models\Alerts;
use app\models\file\JsonFile;
use app\models\AlertsMencions;

use Abraham\TwitterOAuth\TwitterOAuth;
use Codebird\Codebird;



/**
 *
 * @author Eduardo Morales <eduardo@montana-studio.com>
 * @group  Montana-Studio LG 
 */

/**
 * TwitterApi is the model behind the login API.
 *
 */
class TwitterApi extends Model {

	
	private $alertId;
	private $resourcesId;
	private $start_date;
	private $end_date;
	private $country;
	
	private $limit = 0;
	private $minimum = 1;

	private $filename;
	private $resourceName = 'Twitter';
	
	private $products_count;
	
	private $codebird;
	private $data = [];

	/**
	 * [prepare set the property the alert for TwitterApi]
	 * @param  array  $alert  [the alert]
	 * @return [array]        [params for call twitter api]
	 */
	public function prepare($alert = []){
		if(!empty($alert) && ($this->limit > $this->minimum)){
			$this->alertId        = $alert['id'];
			$this->start_date     = $alert['config']['start_date'];
			$this->end_date       = $alert['config']['end_date'];

			$this->country       = (!is_null($alert['config']['country'])) ? $this->_setCountry($alert['config']['country']): null ;
			
			// order products by his  length
			array_multisort(array_map('strlen', $alert['products']), $alert['products']);
			$products   = $alert['products'];
			// set if search finish
			$this->searchFinish();
			// set products
			$products_params = $this->setProductsParams($products);
			

			return $products_params;
		}
		return false;
	}
	/**
	 * [setProductsParams set the params for each products in the alert]
	 * @param array $products [params for call api twitter]
	 */
	public function setProductsParams($products = []){
		
		$products_to_searched = [];
		// set term on cache
		$cache = \Yii::$app->cache;
		$key = "Twitter";
		//$cache->delete("{$key}_{$this->alertId}");
		$data = $cache->get("{$key}_{$this->alertId}");
        $time_expired = (($this->end_date - $this->start_date)) ? $this->end_date - $this->start_date : 86400;

        if ($data === false) {
            // $data is not found in cache, calculate it from scratch
            foreach($products as $index => $product){
                $data[$product] = $this->start_date;
            }
            $cache->set("{$key}_{$this->alertId}", $data, $time_expired);
        } else {
           // $data is found with data
           // if a new terms
           foreach($products as $index => $product){
                if(!isset($data[$product])){
                    $data[$product] = $this->start_date;
                }
            }

		}
		// forming the array params
		$params = [
			'lang' => 'es',
			'result_type' => 'mixed',
			'count' => 100,
		];
		
		for($p = 0; $p < sizeOf($products);$p++){
			$query = (new \yii\db\Query())
		    ->select(['since_id','max_id','date_searched','condition'])
		    ->from('alerts_mencions')
		    ->where([
				'alertId'       => $this->alertId,
				'resourcesId'   => $this->resourcesId,
				'type'          => 'tweet',
				'term_searched' => $products[$p],
		    ])
		    ->one();

		    // Make sure to urlencode any parameter values that contain query-reserved characters
		    $product = urlencode($products[$p]);
		   // $product = $products[$p];
		    $country = (!is_null($this->country)) ? $this->country : '';
		    if($query){
		    	// insert params to the products with condicion active
		    	if($query['condition'] == AlertsMencions::CONDITION_ACTIVE){ 
		    		// pass to variable
		    		list('since_id' => $since_id,'max_id' => $max_id,'date_searched' => $date_searched) = $query;
		    		
					$date_searched_flag   = strtotime(DateHelper::add($this->end_date,'1 day'));

					if($date_searched >= $date_searched_flag){
		    			
		    			continue;
		    		}
		    		
		    		$since_date   = Yii::$app->formatter->asDatetime($date_searched,'yyyy-MM-dd');
					$until_date   = DateHelper::add($date_searched,'1 day');
					//$query_search = "".$product." since:{$since_date} until:{$until_date}";
					$query_search = '"'.$product.'" since:'.$since_date.' until:'.$until_date.'';
					

		    		if($since_id && ($max_id == '')){
						$params['since_id'] = $since_id;
					}

					if($max_id){
						$params['max_id']  = $max_id;
						$params['since_id'] = '';
					}

					$params['q']       = $query_search;
					$params['since']   = $since_date;
					$params['product'] = $products[$p];
					//$params['geocode'] = '-33.459229,-70.645348,50000km';
		    		
					array_push($products_to_searched,$params);
		    	} 
		    }else{

				$productName = $products[$p];

				$since_date = Yii::$app->formatter->asDatetime($data[$productName],'yyyy-MM-dd');
				$until_date = DateHelper::add($data[$productName],'1 day');
		    	//$query_search = "".$product." since:{$since_date} until:{$until_date}";
		    	$query_search = '"'.$product.'" since:'.$since_date.' until:'.$until_date.'';
		    	
		    	
		    	$params['q'] = $query_search;
		    	$params['since'] = $since_date;
		    	//$params['geocode'] = '-33.459229,-70.645348,50000km';
		    	
		    	$params['product'] = $products[$p];
		    	array_push($products_to_searched,$params);
		    }

		}
	
		return $products_to_searched;
		
	}	
	/**
	 * [call loop in to products and call method _getTweets]
	 * @param  array  $products_params [array of products_params]
	 * @return [type]                  [data]
	 */
	public function call($products_params = []){
		for($p = 0; $p < sizeOf($products_params); $p ++){
			$product = $products_params[$p]['product'];
			//Console::stdout("loop in call method {$product}.. \n", Console::BOLD);
			$this->data[$product] = $this->_getTweets($products_params[$p]);
		}
		$this->_orderTweets();

		return (count($this->data)) ? true : false;
	}

	/**
	 * [_getTweets for each param call api twitter]
	 * @param  [type] $params [params product]
	 * @return [type]         [data]
	 */
	private function _getTweets($params){
		
		$data   =[];
		$index  = 0;
		$limit  = 0;
		$sinceId  = null;
		$since_date  = null;
		$until_date = null;
		$max_id = null;

		//var_dump($params);
      	$product = ArrayHelper::remove($params, 'product');
      	$since_date = ArrayHelper::remove($params, 'since');
      	$until_date =  ArrayHelper::remove($params, 'until');
		

      	$properties = [
		  'alertId' => $this->alertId,
		  'resourcesId' => $this->resourcesId,		  
	      'term_searched' => $product,
	      'type' => 'tweet',
		];
		
	    
		do {
			// get data twitter api
			$data[$index] = $this->search_tweets($params);
			//echo $properties['term_searched'];

			if($data[$index]['rate']['remaining'] < $this->minimum){
				// Console::stdout(" limits is: {$this->limit} \n", Console::BOLD);
				// Console::stdout(" remaining is: {$data[$index]['rate']['remaining']} \n", Console::BOLD);
				break;
			}
			// if there 200 status
			if($data[$index]['httpstatus'] == 200){
				//Console::stdout(" is 200 \n", Console::FG_GREEN);
				// if statuses not empty
				if(!empty($data[$index]['statuses'])){
					$statusCount = count($data[$index]['statuses']);
					// check limits
					if(!$this->limit){
						// set limit
						$remaining = $data[$index]['rate']['remaining'];
						$this->limit = $this->_setLimits($remaining);
						//Console::stdout(" set limit: {$this->limit} \n", Console::BOLD);
					}
					// if there sinceId
					if(is_null($sinceId)){
						$sinceId = $data[$index]['statuses'][0]['id'] + 1;

						$since_date = Yii::$app->formatter->asTimestamp($since_date);
						$since_date = intval($since_date);
						if(DateHelper::isToday($since_date)){
							$properties['since_id'] = $sinceId;
							$date_searched = $since_date;
							$this->filename = $since_date;
						}else{
							$date_searched = DateHelper::add($since_date,'1 day');
							$date_searched = Yii::$app->formatter->asTimestamp($date_searched);
						}
						$properties['date_searched'] = Yii::$app->formatter->asTimestamp($date_searched);
						$this->_saveAlertsMencions($properties);
					  
					  //Console::stdout("save one time {$sinceId}.. \n", Console::BOLD);
					}
					// if there next result
					if(ArrayHelper::keyExists('next_results', $data[$index]['search_metadata'], true)){
						// clean next result
						parse_str($data[$index]['search_metadata']['next_results'], $output);
						$params['max_id'] = $output['?max_id']  - 1;
						$lastId = $output['?max_id'];
						//Console::stdout(" is next_results with lastId: {$lastId} \n", Console::BOLD);

						// we are over the limit
						if($this->limit <= $this->minimum){
							$properties['max_id'] = $lastId;
							$date_searched = $since_date;
							$properties['date_searched'] = Yii::$app->formatter->asTimestamp($date_searched);
							//Console::stdout(" limit en minimum: {$this->limit} save properties \n", Console::BOLD);
							$this->_saveAlertsMencions($properties);
						}
					}
					
					// echo "====================". "\n";
					// Console::stdout(" get in array {$this->limit} con params: {$params['q']} \n", Console::BOLD);
					// echo "====================". "\n";
				// empty status	
				}else{
					$properties['max_id'] = '';
					// is date search is today
					$since_date = Yii::$app->formatter->asTimestamp($since_date);
					$since_date = intval($since_date);
					if(DateHelper::isToday($since_date)){
						$properties['since_id'] = $sinceId;
						$date_searched = $since_date;
						$this->filename = $since_date;
					}else{
						$date_searched = DateHelper::add($since_date,'1 day');
						$date_searched = Yii::$app->formatter->asTimestamp($date_searched);
					}
					$properties['date_searched'] = Yii::$app->formatter->asTimestamp($date_searched);
					
					$cache = \Yii::$app->cache;
					$key = "Twitter";
					$time_expired = (($this->end_date - $this->start_date)) ? $this->end_date - $this->start_date : 86400;
					$product_cache = $cache->get("{$key}_{$this->alertId}");
					$product_cache[$product] = $properties['date_searched'];
					$cache->set("{$key}_{$this->alertId}", $product_cache, $time_expired);
					if(AlertsMencions::find()->where([
						'alertId' => $this->alertId,
						'resourcesId' => $this->resourcesId,		  
						'term_searched' => $product,
						'type' => 'tweet',
					])->exists()){
						$this->_saveAlertsMencions($properties);
					}

					
					break;

				}

				//only for testing
				if($this->limit <= $this->minimum){break;}
			// is not 200 httpstatus	
			}else{
				Console::stdout("fail status Twitter {$data[$index]['httpstatus']}.. \n", Console::BOLD);
				// lets go
				break;
			}

			$index++;
			// sub to limit
			$this->limit --;

		}while($this->limit);
        return $data;

	}
	
	/**
	 * [search_tweets call api search/tweet from the api]
	 * @param  array  $params [params to call twitter]
	 * @return [type]         [data]
	 */
	public function search_tweets($params = []){

		$this->codebird->setReturnFormat(CODEBIRD_RETURNFORMAT_ARRAY);
		$this->codebird->setTimeout(4000);
		$this->codebird->setConnectionTimeout(9000);
		return $this->codebird->search_tweets($params, true);
	}

	/**
	 * [_saveAlertsMencions save in alerts_mencions model]
	 * @param  array  $properties [description]
	 * @return [type]             [description]
	 */
	private function _saveAlertsMencions($properties = []){
		
		$model = AlertsMencions::find()->where([
			'alertId'       => $this->alertId,
			'resourcesId'   => $this->resourcesId,
			'type'          => 'tweet',
			'term_searched' => $properties['term_searched']
		])
		->one();

		if(is_null($model)){
			$model = new AlertsMencions();
			$model->alertId = $this->alertId;
			$model->resourcesId = $this->resourcesId;
		}
		foreach($properties as $property => $values){
    		$model->$property = $values;
    	}
    	if(!$model->save()){
    		var_dump($model->errors);
    	}

	}

	/**
	 * [_getProductSearched return from alert_mention table products with condition active o wait]
	 * @param  [type] $product [ej: HD]
	 * @return [type]          [query]
	 */
	private function _getProductSearched($product){
		
		$products_to_searched = [];
		$query = (new \yii\db\Query())
		    ->select(['date_searched', 'max_id','condicion'])
		    ->from('alerts_mencions')
		    ->where([
		    	'alertId' => $this->alertId,
				'resourcesId' => $this->resourcesId,
				//'condition' => AlertsMencions::CONDITION_ACTIVE,
				'type' => 'tweet',
				'term_searched' => $product,
		    ])
		    ->one();
		    if($query){
		    	if($query['condicion'] == AlertsMencions::CONDITION_FINISH){ return false;} 
		    }

		return $query;
	
	}
	/**
	 * [_setLimits divide the total number of limits by the quantity of products]
	 * @param [type] $remaining [description]
	 */
	private function _setLimits($remaining){
		$remaining = $remaining / $this->products_count;
		
		return round($remaining);
	}
	/**
	 * [_orderTweets description]
	 * @param  [type] $data [description]
	 * @return [type]       [description]
	 */
	private function _orderTweets(){
		$tweets = [];
		$source = 'TWITTER';
	
		foreach ($this->data as $product => $object){
			$index = 0;
			for ($o = 0; $o < sizeof($object) ; $o++){
				if(!empty($object[$o]['statuses'])){
					for ($s =0; $s < sizeof($object[$o]['statuses']) ; $s++){
						// source
						$tweets[$product][$index]['source'] = $source;
						// id tweets
						$idTweet = $object[$o]['statuses'][$s]['id'];
						$tweets[$product][$index]['id'] = $object[$o]['statuses'][$s]['id'];
						// get user info
						$user = $this->_getUserData($object[$o]['statuses'][$s]);
						$tweets[$product][$index]['user'] = $user;
						// get entities url
						$tweets[$product][$index]['url'] = $this->_getUrlTweet($idTweet,$user['author_username']);


						if(array_key_exists('place', $object[$o])){
							if(!is_null($object[$o]['place'])){
								$tweets[$product][$index]['location'] = $object[$o]['place']['country'];
							}
						}else{
							$tweets[$product][$index]['location'] = "-";
						}

						$tweets[$product][$index]['created_at'] = $object[$o]['statuses'][$s]['created_at'];
						// get retweet_count
						if(array_key_exists('retweeted_status',$object[$o]['statuses'][$s])){
							$tweets[$product][$index]['retweet_count'] = 0;
						}else{
							$tweets[$product][$index]['retweet_count'] = $object[$o]['statuses'][$s]['retweet_count'];
						}
						// get favorite_count
						$tweets[$product][$index]['favorite_count'] = $object[$o]['statuses'][$s]['favorite_count'];
						// get post_from
						$tweets[$product][$index]['message'] = $object[$o]['statuses'][$s]['text'];
						$tweets[$product][$index]['message_markup'] = $object[$o]['statuses'][$s]['text'];
						
						$index++;
					} // for each statuses
				} // if not empty statuses
			}// for each object twitter
		} // for each product

		$this->data = $tweets;
	}

	/**
	 * [_getUserData get data user from the json]
	 * @param  [type] $tweet [tweet obejct]
	 * @return [type]        [array]
	 */
	private function _getUserData($tweet){
		$data_user = [];
		if(!empty($tweet['user'])){
			$data_user['user_id']         = $tweet['user']['id']; 
			$data_user['author_name']     = \app\helpers\StringHelper::remove_emoji($tweet['user']['name']); 
			$data_user['author_username'] = \app\helpers\StringHelper::remove_emoji($tweet['user']['screen_name']);
			$data_user['profile_image_url']  = $tweet['user']['profile_image_url'];
			$data_user['location']        = $tweet['user']['location'];
			$data_user['description']     = $tweet['user']['description'];
			$data_user['url']             = $tweet['user']['url'];
			$data_user['followers_count'] = $tweet['user']['followers_count'];
			$data_user['friends_count']   = $tweet['user']['friends_count'];
		}
		return $data_user;
	}

	/**
	 * [_getUrlTweet compose url  from url]
	 * @param  [int] $idTweet [tweet id]
	 * @param  [string] $user [user  screen_name]
	 * @return [type]        [array]
	 */
	private function _getUrlTweet($idTweet,$screen_name){
		$entities['url'] = "https://twitter.com/${screen_name}/status/${idTweet}";
		return $entities;
	}

	private function searchFinish(){
    
    	$alertsMencions = \app\models\AlertsMencions::find()->where([
    		'alertId'       => $this->alertId,
	        'resourcesId'   => $this->resourcesId,
	        'type'          => 'tweet',
	        //'condition'		=> 'ACTIVE'
    	])->all(); 

	    $params = [
	        'Twitter' => [
	            'resourceId' => $this->resourcesId,
	            'status' => 'Pending'
	        ]
	    ];

	    if (count($alertsMencions)) {
	    	$count = 0;
	    	$date_searched_flag   = strtotime(\app\helpers\DateHelper::add($this->end_date,'1 day'));
	    	//$date_searched_flag   = $this->end_date;
	      	foreach ($alertsMencions as $alert_mention) {
	      		if($alert_mention->date_searched >= $date_searched_flag){
					$alert_mention->condition = 'INACTIVE';
					$alert_mention->save();
					$count++;
      			}
	      	}
	      	if($count >= count($alertsMencions)){
	          $params['Twitter']['status'] = 'Finish'; 
	        }
	    }  

      \app\helpers\HistorySearchHelper::createOrUpdate($this->alertId, $params);

    }


	private function _setCountry($country){
		
		$country = json_decode($country,true);
		$key     = key($country);
		$geo     = implode(",",$country[$key]);
		return $geo;
	}

	public function saveJsonFile(){

		if(!is_null($this->data)){
			$jsonfile = new JsonFile($this->alertId,$this->resourceName);
			$jsonfile->load($this->data);
			if ($this->filename) {
				$jsonfile->fileName = $this->filename;
			}
			$jsonfile->save();
		}

	}
	/**
	 * [_getTwitterLogin login to twitter]
	 * @return [type] [description]
	 */
	private function _getTwitterLogin(){

		$credencials_api = (new \yii\db\Query())
		    ->select('api_key,api_secret_key,bearer_token')
		    ->from('credencials_api')
		    ->where(['resourceId' => $this->resourcesId])
		    ->all();
		if($credencials_api){
			$bearer_token = ArrayHelper::getColumn($credencials_api,'bearer_token')[0];
			if($bearer_token == ''){
				$api_key = ArrayHelper::getColumn($credencials_api,'api_key')[0];    
				$api_secret_key = ArrayHelper::getColumn($credencials_api,'api_secret_key')[0]; 
				$bearer_token = $this->_getBearerToken($api_key,$api_secret_key);
				if($bearer_token){
					$this->_setBearerToken($bearer_token);
				}
			}else{
				Codebird::setBearerToken($bearer_token);
				$this->codebird = Codebird::getInstance();
			} 
		}    

	}
	/**
	 * [_getBearerToken get the bearer_token]
	 * @param  [type] $api_key        [description]
	 * @param  [type] $api_secret_key [description]
	 * @return [type]                 [description]
	 */
	private function _getBearerToken($api_key,$api_secret_key){
		
		Codebird::setConsumerKey($api_key, $api_secret_key); // static, see README
		$this->codebird = Codebird::getInstance();
		$reply = $this->codebird->oauth2_token();
		$bearer_token = $reply->access_token;
		
		return $bearer_token;

	}
	/**
	 * [_setBearerToken set bearer_token in the database]
	 * @param [type] $bearer_token [description]
	 */
	private function _setBearerToken($bearer_token){
		
		// INSERT (table name, column values)
		Yii::$app->db->createCommand()->update('credencials_api', [
		    'bearer_token' => $bearer_token,
		],'resourceId = 1')->execute();
	}

	private function _getTwitterLimit(){
		
		$this->codebird->setReturnFormat(CODEBIRD_RETURNFORMAT_ARRAY);
		$this->codebird->setTimeout(4000);
		$this->codebird->setConnectionTimeout(9000);

		$data = $this->codebird->application_rateLimitStatus(['resources' => 'search'],true);
		$remaining = 0;
		if(count($data)){
			if(\yii\helpers\ArrayHelper::keyExists('resources', $data)){
				$remaining = ArrayHelper::getValue($data,'resources.search./search/tweets.remaining');
			}
		}
		return $remaining;
	}

	function __construct($products_count = 0){
		// set resource 
		$this->resourcesId = \app\helpers\AlertMentionsHelper::getResourceIdByName("Twitter");
		// get twitter login api
		$this->_getTwitterLogin();
		// set limit
		$this->products_count = $products_count;
		$remaining = $this->_getTwitterLimit();
		$this->limit = $this->_setLimits($remaining);
		// call the parent __construct
		parent::__construct(); 
	}
}

?>