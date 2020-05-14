<?php
namespace app\models\api;

use Yii;
use yii\base\Model;
use yii\helpers\ArrayHelper;
use yii\helpers\Console;

use Goutte\Client;
use GuzzleHttp\Client as GuzzleClient;
use Symfony\Component\DomCrawler\Crawler;


/**
 * class wrapper to web scraping
 */
class Scraping extends Model
{
	public $resourceName = 'Paginas Webs';
	private $alertId;
	private $resourcesId;
	private $terms;
	private $start_date;
	private $end_date;
	private $urls;
	private $data;

	const TYPE_MENTIONS = 'web';
	
    /**
     * [prepare set all internal properties]
     * @param  [array] $alert [alert to run]
     * @return [void]
     */
	public function prepare($alert)
	{
		if(!empty($alert)){
			$this->alertId        = $alert['id'];
			$this->start_date     = $alert['config']['start_date'];
			$this->end_date       = $alert['config']['end_date'];
			// order products by his  length
			array_multisort(array_map('strlen', $alert['products']), $alert['products']);
			$this->terms   = $alert['products'];
			// set if search finish
			$this->searchFinish();
			
			
			$this->urls = $this->_setUrls($alert['config']['urls']);
		}
	}
	/**
	 * [_setUrls get http request to url and extract all links with the same domain the url]
	 * @param  [string] $urls_string [string urls on string]
	 * @return [array]  $urls [set url when url + domain + links]
	 */
	private function _setUrls($urls_string)
	{
		$valid_urls = \app\helpers\StringHelper::getValidUrls($urls_string);
		// get all sub links by each url
		$urls = \app\helpers\ScrapingHelper::getLinksInUrlsWebPage($valid_urls);
	
		return $urls;
	}

	/**
	 * [getRequest get url and send request to transfrom in crawler instance]
	 * @return [array] [crawlers instaces]
	 */
	public function getRequest()
    {
        $client = new Client();
        

        $guzzleClient = new \GuzzleHttp\Client(array(
        	'verify' => Yii::getAlias('@cacert'),
	        'curl' => array(
	        	CURLOPT_SSLVERSION => CURL_SSLVERSION_TLSv1_2,
	           // CURLOPT_FOLLOWLOCATION => true,
	          //  CURLOPT_SSL_VERIFYHOST => false,
	            CURLOPT_SSL_VERIFYPEER => false
	        )
	    ));
	    $client->setClient($guzzleClient);
        $crawler = [];


        
        if (!empty($this->urls)) {
            foreach ($this->urls as $url => $values) {
                if (!empty($values['links'])) {
                    for ($l=0; $l < sizeof($values['links']) ; $l++) { 
                        $link = $values['links'][$l];
                        try {
						    $response = $client->request('GET',$link);
						    $status_code = $client->getResponse()->getStatus();
						    
						    if ($status_code == 200) {
						        $domain = $values['domain'];
						        if($domain){
						            $content_type = $client->getResponse()->getHeader('Content-Type');
						            if (strpos($content_type, 'text/html') !== false) {
						                $crawler[$url][$link][] = $response;
						            }
						        }// if domain
						    }// end if status code    
						} catch (\GuzzleHttp\Exception\ConnectException $e) {
						    // var_dump($e);
						     continue;
						}
                    }// end loop for links
                }// end if empty
            }// end loop foreach
        }// end if empty
        return $crawler;
    }
	
	/**
	 * [searchTermsInContent search terms in the content extract in the web pages]
	 * @param  [array] $data  [array data with his links and content]
	 * @return [array] $model [array with the sentences order by terms]
	 */
	public function searchTermsInContent($data)
	{
		$model = [];
		$terms = $this->terms;

		$properties = [
			'alertId'       => $this->alertId,
			'resourcesId'   => $this->resourcesId,
			'date_searched' => \app\helpers\DateHelper::getToday(),
			'type'          => self::TYPE_MENTIONS,
		];
		

		if (!empty($data)) {
			foreach ($data as $url => $values) {
				//echo $url."\n";
				foreach ($values as $link => $nodes) {
					//echo $link."\n";
					for ($n=0; $n < sizeof($nodes) ; $n++) { 
						//echo $nodes[$n]."\n";
						$sentence = $nodes[$n];
						for ($t=0; $t <sizeof($terms) ; $t++) { 

							$isContains = \app\helpers\StringHelper::containsCountIncaseSensitive($sentence,$terms[$t]);
							if ($isContains) {
								if (!ArrayHelper::keyExists($terms[$t], $model, false)) {
									$model[$terms[$t]] = [];
								}
								$register = [
									'source' => [
										'name' => \app\helpers\StringHelper::getDomain($link)
									],
									'url' => $link,
									'content' => $sentence,
									'message_markup' => $sentence
								];
								if (!in_array($register, $model[$terms[$t]])) {
									$model[$terms[$t]][] = $register;
									$properties['term_searched'] = $terms[$t];
									$properties['url'] = $url;
									$this->_saveAlertsMencions($properties);
								}
							}else{
								$ismodel = \app\models\AlertsMencions::find()->where([
									'alertId'       => $this->alertId,
									'resourcesId'   => $this->resourcesId,
									'type'          => self::TYPE_MENTIONS,
									'term_searched' => $terms[$t],
									'url'			=> $link
								])->one();
								if (!is_null($ismodel)) {
									$ismodel->date_searched = \app\helpers\DateHelper::getToday();
									$ismodel->save();
								}
							}
						}
					}// end loop nodes
				}// end loop values
			}// end loop end data
		}// end if emty data
		$this->data = $model;
		return (!empty($this->data)) ? true : false;
	}

	/**
	 * [_saveAlertsMencions save in alerts_mencions model]
	 * @param  array  $properties [description]
	 * @return [type]             [description]
	 */
	private function _saveAlertsMencions($properties = []){
		
		$model =  \app\models\AlertsMencions::find()->where([
			'alertId'       => $this->alertId,
			'resourcesId'   => $this->resourcesId,
			'type'          => self::TYPE_MENTIONS,
			'term_searched' => $properties['term_searched']
		])
		->one();

		if(is_null($model)){
			$model = new \app\models\AlertsMencions();
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
	 * [searchFinish look up on alert_mentions table the date searched and compare end_date and determine if the mention active/inactive]
	 * @return [void]
	 */
	private function searchFinish(){
    
    	$alertsMencions = \app\models\AlertsMencions::find()->where([
    		'alertId'       => $this->alertId,
	        'resourcesId'   => $this->resourcesId,
	        'type'          => 'web',
    	])->all(); 

	    $params = [
	        'Paginas Webs' => [
	            'resourceId' => $this->resourcesId,
	            'status' => 'Pending'
	        ]
	    ];

	    if (count($alertsMencions)) {
	    	$count = 0;
	    	$date_searched_flag   = $this->end_date;
	      	foreach ($alertsMencions as $alert_mention) {
	      		$date_searched_flag = intval($date_searched_flag);
	      		if (!\app\helpers\DateHelper::isToday($date_searched_flag)) {
	      			if($alert_mention->date_searched >= $date_searched_flag){
	      				$alert_mention->condition = 'INACTIVE';
	      				$count++;
	      			}else{
	      				$alert_mention->condition = 'ACTIVE';
	      			}
	      			$alert_mention->save();
	      		}
	      	}
	      	if($count >= count($alertsMencions)){
	          $params['Paginas Webs']['status'] = 'Finish'; 
	        }
	    }  
      	\app\helpers\HistorySearchHelper::createOrUpdate($this->alertId, $params);

    }

	/**
	 * [saveJsonFile save a json file]
	 * @return [none] [description]
	 */
	public function saveJsonFile(){

		if(!empty($this->data)){
			$jsonfile = new \app\models\file\JsonFile($this->alertId,$this->resourceName);
			$jsonfile->load($this->data);
			$jsonfile->save();
		}

	}
	


	function __construct(){
		$this->resourcesId = \app\helpers\AlertMentionsHelper::getResourceIdByName($this->resourceName);
		// call the parent __construct
		parent::__construct();
	}
}
