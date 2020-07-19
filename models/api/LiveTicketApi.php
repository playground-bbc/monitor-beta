<?php
namespace app\models\api;

use Yii;
use yii\base\Model;
use yii\helpers\Console;
use yii\base\ErrorException;
use LiveChat\Api\Client as LiveChat;
use yii\helpers\ArrayHelper;
use app\models\file\JsonFile;

class LiveTicketApi extends Model {

	public $userId;
	public $alertId;
	public $end_date;
	public $start_date;
	public $resourcesId;
	public $products;
	
	public $data;
	public $filename;


	private $_api_login;
	private $_access_secret_token;

	private $_client;


	/**
	 * [prepare set the property the alert for LiveTicketApi]
	 * @param  array  $alert  [the alert]
	 * @return [array]        [params for call LiveTicketApi]
	 */
	public function prepare($alert = []){
		if(!empty($alert)){
			
			$this->alertId        = $alert['id'];
			$this->start_date     = $alert['config']['start_date'];
			$this->end_date       = $alert['config']['end_date'];
			// order products by his  length
			array_multisort(array_map('strlen', $alert['products']), $alert['products']);
			$this->products   = $alert['products'];
			// set if search finish
			$this->searchFinish();
			// set products
			return $this->_setParams();
		}
		return false;
	}



	/**
	 * [_setParams set params to build the call]
	 */
	private function _setParams(){

		$params = [];

		
		for($p = 0; $p < sizeof($this->products); $p++){
			$productName = $this->products[$p];
			$productMention = $this->_getAlertsMencionsByProduct($productName);
			
			
			if(!$productMention){
				
				$date_from = Yii::$app->formatter->asDate($this->start_date,'yyyy-MM-dd');
				$date_to = \app\helpers\DateHelper::add($this->start_date,'+1 day');



				$params[$productName] = [
					'query'     => $productName,
					'date_from' => $date_from,
					'date_to'   => $date_to,
					'page'      => 1
				];
				
				// set AlertsMencions
				$this->_setAlertsMencionsByProduct($productName);
				
			}else{
				// insert params to the products with condicion active
				if($productMention->condition == \app\models\AlertsMencions::CONDITION_ACTIVE){

					if($productMention->date_searched < $this->end_date)
					{

						if(!\app\helpers\DateHelper::isToday(intval($productMention->date_searched))){
							$date_from = Yii::$app->formatter->asDate($productMention->date_searched,'yyyy-MM-dd');
							$date_to = \app\helpers\DateHelper::add($productMention->date_searched,'+1 day');


							$params[$productName] = [
								'query'  => $productName,
								'date_from' => $date_from,
								'date_to'   => $date_to,
							];

							$newDateSearch = \app\helpers\DateHelper::add($productMention->date_searched,'+1 day');
							$productMention->date_searched = strtotime($newDateSearch);
							$productMention->save();
						}else{

							$date_from = Yii::$app->formatter->asDate($productMention->date_searched,'yyyy-MM-dd');
							$date_to = \app\helpers\DateHelper::add($productMention->date_searched,'+1 day');


							$params[$productName] = [
								'query'  => $productName,
								'date_from' => $date_from,
								'date_to'   => $date_to,
							];

						}

					}else{
						$date_from = \app\helpers\DateHelper::add($productMention->date_searched,'-1 day');


						$params[$productName] = [
							'query'  => $productName,
							'date_from' => $date_from,
							'date_to'   => Yii::$app->formatter->asDate($this->end_date,'yyyy-MM-dd'),
						];

					}

					if (\app\helpers\DateHelper::isToday($productMention->date_searched)) {
						// set filename
						$this->filename = $productMention->date_searched;
					}

				}
			}

		} // end for products
		return $params; 
	}


	/**
	 * [call loop in to products and call method _getTweets]
	 * @param  array  $products_params [array of products_params]
	 * @return [type]                  [data]
	 */
	public function call($products_params = []){
		foreach($products_params as $productName => $params){
			$this->data[$productName] =  $this->_getTickets($params);
		}
		$tickets = $this->_orderTickets($this->data);
		return $tickets;
	}


	private function _getTickets($params){

		$data = [];
		$page = 1;

		$client = $this->_getClient();
		//$productsNames = ArrayHelper::remove($params, 'products');
		

		do{
			// set page 
			$params['page'] = $page;
			//echo "page : {$params['page']} ". "\n";
			try {
				sleep(1);
				$response = $client->tickets->get($params);
			} catch (ErrorException $e) {
				\Yii::warning("Erro Livechat ticket id: {$this->alertId}.");
				var_dump($e->getMessage(),$this->alertId);
			}
			//echo "searching start date". $params['date_from']. " to  ". $params['date_to']. " in productName: ".$params['query']. "\n";
			

			if(count($response->tickets)){
				// get the data
				$data[] = $response->tickets;

			}
			
			$pageresponse = $response->pages;
			$page++;
			// echo "page response: {$pageresponse} ". "\n";
			// echo "page : {$page} ". "\n";

		}while($pageresponse >= $page);

		return $data;

	}


	private function _orderTickets($data){
		$model = [];
		$tmp = [];
		foreach($data as $productName => $groupTickets){
			if(count($data[$productName])){
				//$model [$productName] = [];
				foreach($groupTickets as $group => $tickets){
					for($t = 0; $t < sizeof($tickets); $t++){
						//$model[$productName][]  = $this->_exclude($tickets[$t]);
						
						$ticket = $this->_exclude($tickets[$t]);
						if(property_exists($ticket,'events')){

							for($e = 0; $e < sizeOf($ticket->events); $e++){
								
								if(property_exists($ticket->events[$e],'message')){
									if(\app\helpers\DateHelper::isBetweenDate($ticket->events[$e]->date,$this->start_date,$this->end_date)){
										$ticket->events[$e]->message = \app\helpers\StringHelper::collapseWhitespace($ticket->events[$e]->message);
										$ticket->events[$e]->message_markup = $ticket->events[$e]->message;
									}else{
										unset($ticket->events[$e]->message);
									}

									
								}// end if property_exists
							}
						}// end if ArrayHelper
						if(!in_array($tickets[$t]->id,$tmp)){
							$model[$productName][]  = $ticket;
							$tmp [] = $tickets[$t]->id;
							
						}
					}// end loop tickets

				} // end foreach groupTickets
			} // if count
		}// end foreach data

		return $model;
		
	}


	/**
	 * [_getAlertsMencionsByProduct get model by product name]
	 * @param  [type] $productName [name product]
	 * @return [obj / boolean]     [a model if exits or false if not]
	 */
	private function _getAlertsMencionsByProduct($productName){

		$where = [
			//'condition'     => 'ACTIVE',
			'type'          => 'ticket',
			'term_searched' => $productName,
			'alertId'       => $this->alertId,
			'resourcesId'   => $this->resourcesId
		];

		$is_exits = \app\models\AlertsMencions::find()->where($where)->exists();
		
		$model = false;
		
		if($is_exits){
			$model = \app\models\AlertsMencions::find()->where($where)->one();
		}

		return $model;
	
	}


	private function _setAlertsMencionsByProduct($productName){
		
		if (\app\helpers\DateHelper::isToday(intval($this->start_date))) {
			$date_searched = $this->start_date;
		}else{
			$newDateSearch = \app\helpers\DateHelper::add($this->start_date,'+1 day');
			$date_searched = strtotime($newDateSearch);

		}
		

		$model  =  new \app\models\AlertsMencions();
		$model->alertId = $this->alertId;
		$model->resourcesId = $this->resourcesId;
		$model->condition = 'ACTIVE';
		$model->type = 'ticket';
		$model->term_searched = $productName;
		$model->date_searched = $date_searched;
		$model->save();
	}

	/**
	 * [saveJsonFile save a json file]
	 * @return [none] [description]
	 */
	public function saveJsonFile($tickets){

		$source = 'Live Chat';
		if(count($tickets)){
			$jsonfile = new JsonFile($this->alertId,$source);
			$jsonfile->load($tickets);
			if ($this->filename) {
				$jsonfile->fileName = $this->filename;
			}
			$jsonfile->save();
		}

	}

	private function searchFinish()
	{
		$alertsMencions = \app\models\AlertsMencions::find()->where([
    		'alertId'       => $this->alertId,
	        'resourcesId'   => $this->resourcesId,
	        'type'          => 'ticket',
	        //'condition'		=> 'ACTIVE'
    	])->all(); 


		$model = [
            'LiveTicket' => [
                'resourceId' => $this->resourcesId,
                'status' => 'Pending'
            ]
        ];

		if(count($alertsMencions)){
			$count = 0;
			$date_searched_flag   = intval($this->end_date);

			foreach ($alertsMencions as $alert_mention) {
				if (!\app\helpers\DateHelper::isToday($date_searched_flag)) {
					if($alert_mention->date_searched >= $date_searched_flag){
	      				if(!$alert_mention->since_id){
		      				$alert_mention->condition = 'INACTIVE';
		      				$alert_mention->save();
	      					$count++;
	      				}
	      			}
				}
	      	}

			if($count >= count($alertsMencions)){
				$model['LiveTicket']['status'] = 'Finish'; 
			}

		}
		
		\app\helpers\HistorySearchHelper::createOrUpdate($this->alertId, $model);

	}


	/**
     * [_exclude eclude the data that will not be used]
     * @param  [type] $ticket [description]
     * @return [type]         [description]
     */
    private function _exclude($ticket)
    {
        $data = [];
        $exclude = [
        	'resolutionDate',
	        'firstResponse',
	        'ccs',
	        'tags',
	        //'rate',
	        'currentGroup',
	        'opened',
	        'modified',
	        'groups'
        ];
        
        for ($i=0; $i <sizeof($exclude) ; $i++) { 
            if (property_exists($ticket, $exclude[$i])) {
                $property = $exclude[$i];
                unset($ticket->$property);
            }
        }
        return $ticket;
    }

	/**
	 * [_getClient return client http request]
	 * @return [obj] [return object client]
	 */
	private function _getClient(){
		$this->_client = new LiveChat($this->_api_login, $this->_access_secret_token);
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
		    ->where(['name' => 'Live Chat','resourcesId' => $socialId['id']])
		    ->all();
		

		$this->resourcesId = ArrayHelper::getColumn($resourcesId,'id')[0];    
	}

	
	private function _setCredentials(){

		$rows = (new \yii\db\Query())
        ->select(['apiLogin','api_key'])
        ->from('credencials_api')
        ->where(['name_app' => 'monitor-livechat'])
        ->one();

        
		// get the user credentials
		$this->_api_login = $rows['apiLogin'];
		// get token   
		$this->_access_secret_token = $rows['api_key'];

	}


	

	function __construct(){
		
		// set resource 
		$this->_setResourceId();
		// set credencials
		$this->_setCredentials();
		// get client
		$this->_getClient();
		
		parent::__construct(); 
	}

}