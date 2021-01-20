<?php
namespace app\models\api;

use Yii;
use yii\base\Model;
use yii\helpers\ArrayHelper;
use yii\helpers\Console;


use app\models\file\JsonFile;

/**
 *
 * @author Eduardo Morales <eduardo@montana-studio.com>
 * @group  Montana-Studio LG 
 */

/**
 * BaseApi is the model behind the calls to models for API.
 *
 */
class BaseApi extends Model {

	public $alerts;
	public $products_count = 0;
	public $data;

	public $className = [
		'Twitter'                 => 'twitterApi',
		'Facebook Comments'       => 'facebookCommentsApi',
		'Facebook Messages'       => 'facebookMessagesApi',
		'Instagram Comments'      => 'InstagramCommentsApi',
		'Live Chat'               => 'liveChat',
		'Live Chat Conversations' => 'liveChatConversations',
		'Web page'                => 'webpage',
		'Excel Document'          => 'excelDocument',
		'Paginas Webs'            => 'webPages',
	];


	public function callResourcesApi($alerts = []){

		if(!empty($alerts)){
			$resources = [];
			for($a = 0; $a < sizeOf($alerts); $a++){
				array_multisort(array_map('strlen', $alerts[$a]['config']['configSources']), $alerts[$a]['config']['configSources']);
				for($c = 0; $c < sizeOf($alerts[$a]['config']['configSources']); $c++){
					$name = $alerts[$a]['config']['configSources'][$c];
					if(ArrayHelper::keyExists($name,$this->className, false)){
						$className = $this->className[$name];
						$resources[$className][] = $alerts[$a]; 
					}
				}
			}
			foreach($resources as $method => $alerts){
				$this->{$method}($alerts);
			}
			
		} // if alert
	}

	public function twitterApi($alerts = []){
		
		$products_count = $this->countAllTerms($alerts);
		$tweets = new \app\models\api\TwitterApi($products_count);

		foreach ($alerts as $alert) {
			$products_params = $tweets->prepare($alert);
			if($products_params){
				if($tweets->call($products_params)){
					// path to folder flat archives
					$tweets->saveJsonFile();
				}
			}
			
		}
	}

	public function facebookCommentsApi($alerts = []){
		
		$facebookCommentsApi = new \app\models\api\FacebookCommentsApi();

		foreach ($alerts as $alert){
			$query_params = $facebookCommentsApi->prepare($alert);
			if($query_params){
				$facebookCommentsApi->call($query_params);
				$facebookCommentsApi->saveJsonFile();
			}
		}

	}

	public function facebookMessagesApi($alerts = []){

		$facebookMessagesApi = new \app\models\api\FacebookMessagesApi();

		foreach ($alerts as $alert){
			$query_params = $facebookMessagesApi->prepare($alert);
			if($query_params){
				$facebookMessagesApi->call($query_params);
				$facebookMessagesApi->saveJsonFile();
			}
		}

	}

	public function InstagramCommentsApi($alerts = []){
		
		$InstagramCommentsApi = new \app\models\api\InstagramCommentsApi();

		foreach ($alerts as $alert){
			$query_params = $InstagramCommentsApi->prepare($alert);
			if($query_params){
				$InstagramCommentsApi->call($query_params);
				$InstagramCommentsApi->saveJsonFile();
			}
		}

	}

	public function liveChat($alerts = []){
		$LivechatTicketApi = new \app\models\api\LiveTicketApi();

		foreach ($alerts as $alert){
			$query_params = $LivechatTicketApi->prepare($alert);
			
			if($query_params){
				$tickets = $LivechatTicketApi->call($query_params);
				$LivechatTicketApi->saveJsonFile($tickets);
			}
		}

		
	}

	public function liveChatConversations($alerts = []){
		$LiveChatApi = new \app\models\api\LiveChatsApi();

		foreach ($alerts as $alert){
			$query_params = $LiveChatApi->prepare($alert);

			if($query_params){
				$chats = $LiveChatApi->call($query_params);
				$LiveChatApi->saveJsonFile($chats);
			}
		}
	}

	public function excelDocument($alerts = []){
		//echo "excelDocument". "\n";
		
	}

	/**
	 * [webPages call webPages model Api]
	 * @param  array  $alerts [alert with webPages resources]
	 * @return [null]        
	 */
	public function webPages($alerts = [])
	{
		foreach ($alerts as $alert) {
			if ($alert['config']['urls'] != '') {
				$scraping = new \app\models\api\Scraping();
				$query_params = $scraping->prepare($alert);
				//$startTime = microtime(true);
				$crawlers = $scraping->getRequest();
				$content  = \app\helpers\ScrapingHelper::getContent($crawlers);
				$data     = \app\helpers\ScrapingHelper::setContent($content);
				$model    = $scraping->searchTermsInContent($data);
				if(!empty($model)){
					$scraping->saveJsonFile();
				}
			//	echo "Elapsed time on : saveJsonFile ". (microtime(true) - $startTime) ." seconds \n";
			}
		}
	}

	public function countAllTerms($alerts = []){
		$count = 0;
		for($a = 0; $a < sizeOf($alerts); $a++){
			if(ArrayHelper::keyExists('products', $alerts[$a], false)){
				$count += count($alerts[$a]['products']);
			}
		}
		return $count;
	}


	public function saveJsonFile($folderpath = [],$data){

		if(!empty($data)){
			// pass to variable
		    list('source' => $source,'documentId' => $documentId) = $folderpath;
			// call jsonfile
			$jsonfile = new JsonFile($documentId,$source);
			$jsonfile->load($data);
			$jsonfile->save();
		}

	}

	public function readDataResource($alerts = []){
		$alerts= ArrayHelper::map($alerts,'id','config.configSources');
		$data = [];
		
        foreach($alerts as $alertid => $sources){
            foreach ($sources as $source){
                $jsonFile= new JsonFile($alertid,$source);
                if(!empty($jsonFile->findAll())){
                    $data[$alertid][$source] = $jsonFile->findAll();
                }
                    
            }
               
        }
        
        // no empty
        if(!empty($data)){
        	foreach ($data as $alertId => $resources){
        		foreach ($resources as $resource => $values){
					$resourceName = str_replace(" ", "",ucwords($resource));
        			$this->{"readData{$resourceName}Api"}($alertId,$values);
        		}
        	}
        	
        }
       	//change the status if finish
		\app\helpers\AlertMentionsHelper::checkStatusAndFinishAlerts($alerts);

	}


	public function readDataTwitterApi($alertId,$data){
		$searchTwitterApi = new \app\models\search\TwitterSearch();
		$searchTwitterApi->alertId = $alertId;
		
		if(!$searchTwitterApi->load($data)){
			// send email params in twitterApi no load with alertId and count($params)
		}
		if($searchTwitterApi->search()){
			\app\helpers\DocumentHelper::moveFilesToProcessed($alertId,'Twitter');
		}
		
	}


	public function readDataFacebookCommentsApi($alertId,$data){
		$searchFacebookApi = new \app\models\search\FacebookSearch();
		$searchFacebookApi->alertId = $alertId;

		$searchFacebookApi->load($data);

		if($searchFacebookApi->search()){
			//\app\helpers\DocumentHelper::moveFilesToProcessed($alertId,'Facebook Comments');

		}
	}

	public function readDataFacebookMessagesApi($alertId,$data){
		$searchFacebookMessagesApi = new \app\models\search\FacebookMessagesSearch();
		$searchFacebookMessagesApi->alertId = $alertId;

		$searchFacebookMessagesApi->load($data);
		if ($searchFacebookMessagesApi->search()) {
			\app\helpers\DocumentHelper::moveFilesToProcessed($alertId,'Facebook Messages');
		}
		
		
	}

	public function readDataInstagramCommentsApi($alertId,$data){
		$searchInstagramApi = new \app\models\search\InstagramSearch();
		$searchInstagramApi->alertId = $alertId;

		$searchInstagramApi->load($data);
		if($searchInstagramApi->search()){
			\app\helpers\DocumentHelper::moveFilesToProcessed($alertId,'Instagram Comments');

		}
		
	}

	public function readDataExcelDocumentApi($alertId,$data){
		
		$searchExcel = new \app\models\search\ExcelSearch();
		$params = [$alertId,$data];

		$searchExcel->load($params);
		if($searchExcel->search()){
			\app\helpers\DocumentHelper::moveFilesToProcessed($alertId,'Excel Document');

		}

	}

	public function readDataLiveChatApi($alertId,$data){ 
		
		$searchLiveApi = new \app\models\search\LiveTicketSearch(); 
		$searchLiveApi->alertId = $alertId;
		
		$searchLiveApi->load($data); 
		if($searchLiveApi->search()){ 
			\app\helpers\DocumentHelper::moveFilesToProcessed($alertId,'Live Chat'); 
		} 
 
	} 

	public function readDataliveChatConversationsApi($alertId,$data){ 

		$searchLiveChatApi = new \app\models\search\LiveChatSearch(); 
		$searchLiveChatApi->alertId = $alertId;
 
		$searchLiveChatApi->load($data); 
		if($searchLiveChatApi->search()){ 
			\app\helpers\DocumentHelper::moveFilesToProcessed($alertId,'Live Chat Conversations'); 
		} 
 
	}

	/**
	 * [readDataPaginasWebsApi read and search depends on the params the alerts]
	 * @param  [int] $alertId   [id of the alert]
	 * @param  [array] $data    [data from the json file]
	 * @return [null]          
	 */
	public function readDataPaginasWebsApi($alertId,$data)
	{
		$searchScrapingApi = new \app\models\search\ScrapingSearch();
		$searchScrapingApi->alertId = $alertId;

		$searchScrapingApi->load($data);
		if($searchScrapingApi->search()){ 
			\app\helpers\DocumentHelper::moveFilesToProcessed($alertId,'Paginas Webs'); 
		} 
	}

	public function callInsights($userFacebook)
	{
	 	$insightsApi = new \app\models\api\InsightsApi();
	 	
	 	if ($insightsApi->prepare($userFacebook)) {
	 		$page = $insightsApi->getInsightsPageFacebook();
	 		if ($page) {
	 			$insightsApi->setInsightsPageFacebook($page);
	 			
	 		}
	 		$insightsApi->setInsightsPostFacebook();

	 		$pageIns = $insightsApi->getInsightsPageInstagram();
	 		if ($pageIns) {
	 			$insightsApi->setInsightsPageInstagram($pageIns);
	 		}
	 		$insightsApi->setInsightsPostInstagram();
	 		$insightsApi->setStorysPostInstagram();

	 		$insightsApi->setInsightsPostonDbSave();

	 	}
	} 

	
}

?>