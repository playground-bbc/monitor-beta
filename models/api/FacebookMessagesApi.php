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
 * FacebookMessagesApi is the model behind the login API.
 *
 */
class FacebookMessagesApi extends Model {

	public $userId;
	public $alertId;
	public $end_date;
	public $start_date;
	public $resourcesId;
	public $products;
	
	public $data;



	private $_baseUrl = 'https://graph.facebook.com/v4.0';
	
	private $_limit_message = 1;
	
	
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
			
			//get from alermentios
			$alertsMencions = \app\models\AlertsMencions::find()->where([
	    		'alertId'       => $this->alertId,
		        'resourcesId'   => $this->resourcesId,
		        'type'        	=> 'messages Facebook',
		      //  'condition'		=> 'ACTIVE'
	    	])->all();

	    	if (count($alertsMencions)) {
	    		$products = [];
	    		foreach ($alertsMencions as $alertsMencion) {
	    			if(in_array($alertsMencion->term_searched, $alert['products'])){
	    				$index = array_search($alertsMencion->term_searched, $alert['products']);
	    				unset($alert['products'][$index]);
	    			}
	    		}

	    		$alert['products'] = array_values($alert['products']);

	    	}
			////
	    	
		
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
	 * [_setParams set params to build the call]
	 */
	private function _setParams(){

		$params = [];
		// get the user credentials
		$user_credential = \app\helpers\FacebookHelper::getCredencials($this->userId);

		// get page token   
		$this->_page_access_token = \app\helpers\FacebookHelper::getPageAccessToken($user_credential);
		// get appsecret_proof
		$this->_appsecret_proof = \app\helpers\FacebookHelper::getAppsecretProof($this->_page_access_token);
		// loading firts query
		$params['query'] = $this->_messageSimpleQuery();  

		return $params; 

	}


	/**
	 * [call loop in to for each alert and call method _getComments]
	 * @param  array  $query_params   [array of query]
	 * @return [type]                  [data]
	 */
	public function call($query_params = []){

		
		$data = $this->_getDataApi($query_params);

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

		 
		$messages = $this->_getMessages($query_params);
		
		// if there post
		if(count($messages)){
			$filter_messages = $this->_filterFeedsbyProducts($messages);
			$filter_last_messages = $this->_filterByLastMessage($filter_messages);
			$model = $this->_addingMessagesMarkup($filter_last_messages);
			$this->searchFinish();
			return $model;

		}
	}

	/**
	 * [_getMessages get post instagram_business_account]
	 * @param  [array] $query_params [description]
	 * @return [array]               [feeds]
	 */
	private function _getMessages($query_params){
		$client = $this->_client;

		// simple query
		if(\yii\helpers\ArrayHelper::keyExists('query', $query_params, false) ){
			
			$after = '';
			$index = 0;
			$responseData = [];
			
			do{
				
				try{
					// lets loop if next in post or comments and there limit facebook
					$messagesResponse = $client->get($query_params['query'],[
						'after' => $after,
						'access_token' => $this->_page_access_token,
						'appsecret_proof' => $this->_appsecret_proof
					])
					->setOptions([
			        'timeout' => 10, // set timeout to 10 seconds for the case server is not responding
			    	])->send();

			    	$responseHeaders = $messagesResponse->headers->get('x-business-use-case-usage'); // get headers
			    	// if get error data
					if(\yii\helpers\ArrayHelper::getValue($messagesResponse->getData(),'error' ,false)){
						var_dump($messagesResponse->getData());
						// send email with data $responseData[$index]['error']['message']
						break;
					}

					// is over the limit
					if(\app\helpers\FacebookHelper::isCaseUsage($messagesResponse)){
						break;
					}

					// get the after
					if(\yii\helpers\ArrayHelper::getValue($messagesResponse->getData(),'paging.next' ,false)){ // if next
						$after = \yii\helpers\ArrayHelper::getValue($messagesResponse->getData(),'paging.cursors.after' ,false);
						$is_next = true;
					}else{
						$is_next = false;
					} 

					$data =  $messagesResponse->getData(); // get all post and comments


					if(isset($data['data'][0]['messages']['data'][0]['created_time'])){
						
						$date_comment = $data['data'][0]['messages']['data'][0]['created_time'];
						$end_date = strtotime(\app\helpers\DateHelper::add($this->end_date,'+1 day'));


						$date_comment_unix = strtotime($date_comment);

						if(\app\helpers\FacebookHelper::isPublicationNew($this->start_date,$date_comment_unix)){
							$between = true;
						}else{
							$between = false;
						}

						if(\app\helpers\DateHelper::isBetweenDate($date_comment,$this->start_date,$end_date)){
							
							$responseData[$index] = $data;
							$index++;
						}


					}else{
						break;
					}



				}catch(\yii\httpclient\Exception $e){
					// send a email with no internet connection
					 echo 'Excepción capturada: ',  $e->getMessage(), "\n";
					 die();
				}

		    	

			}while($is_next && $between);


			return $responseData ;

		}
	}

	/**
	 * [_filterFeedsbyProducts filter feeds by products]
	 * @param  [array] $feeds [data feeds]
	 * @return [array] $feeds [feed filter]
	 */
	private function _filterFeedsbyProducts($messages){
		$data = [];
		$messages_count = count($messages);

		// params to save in AlertMentionsHelper and get
		$where = [
			'condition'   => 'ACTIVE',
			'type'        => 'messages Facebook',
			'alertId'     => $this->alertId,
			'resourcesId' => $this->resourcesId,
		];


		for($m = 0; $m < sizeOf($messages); $m++ ){
			for($d = 0 ; $d < sizeOf($messages[$m]['data']); $d++){
				$message_id =  $messages[$m]['data'][$d]['id'];
				$url_link =  $messages[$m]['data'][$d]['link'];
				$id_recolect = [];
				for($p = 0; $p < sizeof($this->products); $p++){
					for($c = 0; $c < sizeOf($messages[$m]['data'][$d]['messages']['data']); $c++){

						$message =  $messages[$m]['data'][$d]['messages']['data'][$c]['message'];
						if(!empty($message)){
							$created_time = $messages[$m]['data'][$d]['messages']['data'][$c]['created_time'];
							if(\app\helpers\DateHelper::isBetweenDate($created_time,$this->start_date,$this->end_date)){
								$messages[$m]['data'][$d]['messages']['data'][$c]['url'] = $url_link; 
								// destrutura el product
								$product_data = \app\helpers\StringHelper::structure_product_to_search($this->products[$p]);
								// if mentions products
								$is_contains =  \app\helpers\StringHelper::containsAny($message,$product_data);
								
								if($is_contains){
									if(!in_array($message_id, $id_recolect)){
										if(!ArrayHelper::keyExists($this->products[$p], $data, false)){
										$data[$this->products[$p]] = [] ;
										}
										if(!ArrayHelper::keyExists($message_id,$data[$this->products[$p]], false)){

											//$data[$this->products[$p]][$message_id] = $messages[$m]['data'][$d]['messages']['data'];
											$filter_messages =  $this->_filterByDate($messages[$m]['data'][$d]['messages']['data']);
											if(!empty($filter_messages)){
												$data[$this->products[$p]][$message_id] = $filter_messages;
											}
											$where['publication_id'] = $message_id;
											\app\helpers\AlertMentionsHelper::saveAlertsMencions($where,['term_searched' => $this->products[$p]]);
											$id_recolect[] = $message_id;

										}
									}

								}// end if contains
							}
						}// end if not empty

					} // end for messages data
				} // end for products
			}// end for data
		}// end for messages
		return $data;

	}

	private function _filterByDate($messages){
		$data = [];
		for($c = 0; $c < sizeOf($messages); $c++){
			if(\app\helpers\DateHelper::isBetweenDate($messages[$c]['created_time'],$this->start_date,$this->end_date)){
				$data[] = $messages[$c];
			}
		}
		return $data;
	}


	private function _filterByLastMessage($messages){
		// params to save in AlertMentionsHelper and get
		$model = [];
		$where = [
			'condition'   => 'ACTIVE',
			'type'        => 'messages Facebook',
			'alertId'     => $this->alertId,
			'resourcesId' => $this->resourcesId,
		];

		$query = \app\helpers\AlertMentionsHelper::getAlersMentions($where);
		if($query){
			$params['messages'] = ArrayHelper::index($query,'publication_id');
		}

		
		foreach ($messages as $product => $values){
			foreach($values as $comment_id => $data ){
				$date_searched = $params['messages'][$comment_id]['date_searched'];
				
				if($date_searched != null){
					for($d = 0; $d < sizeOf($data); $d ++){
						$date_comment_unix = strtotime($data[$d]['created_time']);
						if(!\app\helpers\FacebookHelper::isPublicationNew($date_searched,$date_comment_unix)){
							unset($messages[$product][$comment_id][$d]);
							
						}else{
							$where['publication_id'] = $comment_id;
							$unix_time = \app\helpers\DateHelper::asTimestamp($data[$d]['created_time']);
							$date = strtotime("+1 seconds",$unix_time);
							\app\helpers\AlertMentionsHelper::saveAlertsMencions($where,['term_searched' => $product,'date_searched' => $date]);

						}
					}
				}else{
					$where['publication_id'] = $comment_id;
					$unix_time = \app\helpers\DateHelper::asTimestamp($data[0]['created_time']);
					$date = strtotime("+1 seconds",$unix_time);
					\app\helpers\AlertMentionsHelper::saveAlertsMencions($where,['term_searched' => $product,'date_searched' => $date]);
				}
			}
		}// end foreach
		return $messages;

	}


	private function _addingMessagesMarkup($messages){

		foreach ($messages as $product => $ids_messages){
			foreach ($ids_messages as $ids_message => $msg){
				for($m =  0; $m < sizeof($msg); $m++ ){
					$tmp = $msg[$m]['message'];
					$messages[$product][$ids_message][$m]['message_markup'] = $tmp;

				}
			}
		}

		return $messages;

	}


	private function _messageSimpleQuery(){
		$bussinessId = Yii::$app->params['facebook']['business_id'];
		
		$message_query = "{$bussinessId}/conversations?fields=link,message_count,name,updated_time,messages{message,from,created_time,updated_time}&limit={$this->_limit_message}";

		return $message_query;
	}

	/**
	 * [saveJsonFile save a json file]
	 * @return [none] [description]
	 */
	public function saveJsonFile(){
		$source = 'Facebook Messages';
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
            'Facebook Messages' => [
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
        		'type' => 'messages Facebook'
        	])->all();
        	
        	if (count($alermentions)) {
        		foreach ($alermentions as $alermention) {
	        		$alermention->condition = 'INACTIVE';
	        		$alermention->save();
	        	}
        	}
        	$model['Facebook Messages']['status'] = 'Finish'; 
        }

		
		\app\helpers\HistorySearchHelper::createOrUpdate($this->alertId, $model);

	}

	/**
	 * [_getClient return client http request]
	 * @return [obj] [return object client]
	 */
	private function _getClient(){
		$this->_client = new Client(['baseUrl' => $this->_baseUrl]);
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
		    ->where(['name' => 'Facebook Messages','resourcesId' => $socialId['id']])
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