<?php
namespace app\helpers;

use yii;
use TextAnalysis\Filters\StopWordsFilter;
use StopWordFactory;

/**
 * @author Eduardo Morales <eduardo@montana-studio.com>
 * ScrapingHelper wrapper for scraping function.
 *
 */
class ScrapingHelper{

	/**
     * [rules for scrapping a webpage]
     * @return [array] [title => rule xpath]
     */
    public static function rules()
    {
        return [
           // '//title'       => Yii::t('app','document_title'),
            '//h1'          => Yii::t('app','cabezera_1'),
            '//h2'          => Yii::t('app','cabezera_2'),
            '//h3'          => Yii::t('app','cabezera_3'),
            '//h4'          => Yii::t('app','cabezera_4'),
            '//h5'          => Yii::t('app','cabezera_5'),
            '//strong'      => Yii::t('app','negrita'),
           //'//a'           => Yii::t('app','link'),
           '//b'           => Yii::t('app','negrita'),
            '//span'        => Yii::t('app','contenedor'),
            '//ul//li'      => Yii::t('app','ítem'),
            //'//address'     => Yii::t('app','address'),
            //'//aside'       => Yii::t('app','aside'),
           '//hgroup'      => Yii::t('app','hgroup'),
            '//p'           => Yii::t('app','paragraph'),
        ];
    }

	/**
	 * [getLinksInUrlsWebPage get all sub links on same domain]
	 * @param  array  $valid_urls [urls valids]
	 * @return [array]             [all the sublinks by each url]
	 * $urls = [
			'https://www.forbes.com'=>[
				'domain' => 'chron.com',
				'links'  => [
					'https://www.chron.com'
				],
			]
		];
	 */
	public static function getLinksInUrlsWebPage($valid_urls = [])
	{
		$urls = [];
		// Initialize the client with the handler option
		$client = new \GuzzleHttp\Client();
		// loop on urls
		foreach ($valid_urls as $url) {
			$domain = \app\helpers\StringHelper::getDomain($url);
			try {
				
				$request = new \GuzzleHttp\Psr7\Request('get', $url);
				$response = $client->send($request, ['timeout' => 10]);
				
				$code = $response->getStatusCode();
				$reason = $response->getReasonPhrase(); // OK
				
				if ($code == 200 && $reason == 'OK') {
					$body = $response->getBody()->getContents();
					// call crallwer
					$crawler = new \Symfony\Component\DomCrawler\Crawler($body,$url);
					$links_count = $crawler->filter('a')->count();
					if ($links_count > 0) {
						$links = $crawler->filter('a')->links();
						
						$all_links = [];
						// put original url
						if (!in_array($url, $all_links)) {
							array_push($all_links, $url);
						}
						foreach ($links as $link) {
							$link_web = $link->getURI();
							$link_same_domain = \app\helpers\StringHelper::getDomain($link_web);
						    if($domain == $link_same_domain){
							  $url_without_query_string = strtok($link_web, '?');
						      $all_links[] = $url_without_query_string;  
						    }
						    
						} // for each links
						
						$all_links = array_unique($all_links);
						// reorder array
						$links_order = array_values($all_links);
						$urls[$url]['domain'] = $domain;
						$urls[$url]['links'] = $links_order;
					}
				}
			} catch (\GuzzleHttp\Exception\RequestException $e) {
				// send email
				//echo "TooManyRedirectsException: ".$e->getMessage();
				continue;
			}
		}
		// $urls = [
		// 	'https://www.lg.com'=>[
		// 		'domain' => 'lg.com',
		// 		'links'  => [
		// 			'https://www.lg.com/es'
		// 		],
		// 	]
		// ];
		return $urls;
		
	}

	/**
	 * [getOrSetUrlsFromCache return urls from cache]
	 * @param  array  $urls [urls valids]
	 * @param  array  $id   [id from alert]
	 * @return [array]      [all the sublinks by each url]
	 *
	 */
	public static function getOrSetUrlsFromCache($urls,$key,$id)
	{
		// ver si un nueva url no esta en cache y agregarla con value 0
		$cache = \Yii::$app->cache;
		$data = $cache->get("{$key}_{$id}");
		$time_expired = 86400; // seconds in a days
		$urls_keys = array_keys($urls);
		if ($data === false) {
            // $data is not found in cache, calculate it from scratch
            foreach($urls_keys as $index => $url){
                $data[$url] = 0;
            }
            $cache->set("{$key}_{$id}", $data, $time_expired);
        } else {
           // $data is found with data
           // if a new url
           foreach($urls_keys as $index => $url){
                if(!isset($data[$url])){
                    $data[$url] = 0; 
                }
            }

		}
		
		$new_url = [];
        foreach ($urls as $url => $properties) {
            $limit = (sizeOf($properties['links']) > 1) ? 5 : sizeOf($properties['links']); 
            $index = $data[$url];
            $tmp = 0;
            $new_url[$url]['domain'] = $properties['domain'];
            for ($i=$index; $i < sizeOf($properties['links']) ; $i++) { 
                if($tmp >= $limit){
                    break;
                }
                $new_url[$url]['links'][] = $properties['links'][$i];
                $tmp++;
            }
            if($i >= sizeOf($properties['links'])){
                $data[$url] = 0;
            }else{
                $data[$url] = $i;
            }
            
		}
		$cache->set("{$key}_{$id}", $data, $time_expired);
		return $new_url;
	}

	/**
	 * [getRequest get url and send request to transfrom in crawler instance]
	 * @return [array] [crawlers instaces]
	 */
	public static function getRequest($urls)
    {
        $client = new \Goutte\Client();

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

		//$startTime = microtime(true);

        if (!empty($urls)) {
            foreach ($urls as $url => $values) {
                if (!empty($values['links'])) {
                    for ($l=0; $l < sizeof($values['links']) ; $l++) { 
						$link = $values['links'][$l];
                        try {
						    $response = $client->request('GET',$link);
						    $status_code = $client->getResponse()->getStatus();

						    if ($status_code == 200) {
						        $content_type = $client->getResponse()->getHeader('Content-Type');
								if (strpos($content_type, 'text/html') !== false) {
									$crawler[$url][$link][] = $response;
								}
						    }// end if status code    
						} catch (\GuzzleHttp\Exception\ConnectException $e) {
						    // var_dump($e);
						     continue;
						}
                    }// end loop for links
                }// end if empty
            }// end loop foreach
		}// end if empty
		//echo "Elapsed time on : getRequest ". (microtime(true) - $startTime) ." seconds \n";
        return $crawler;
    }
	/**
	 * [getContent loop on each link to filter for his crawler]
	 * @param  [array] $crawlers [url and his links with each craw]
	 * @return [array] $contents [each link with content by rules]
	 */
	public static function getContent($crawlers)
	{
		$contents = [];
        
        foreach ($crawlers as $url => $links) {
        	foreach ($links as $link => $crawler) {
        		for ($c =0; $c  < sizeof($crawler) ; $c ++) { 
        			foreach (self::rules() as $rule => $title){
        				$contents[$url][$link][] = $crawler[$c]->filterXpath($rule)->each(function ($node,$i) use ($rule)
	                    {
	                    	$text = $node->text();
	                    	if (!\app\helpers\StringHelper::isEmpty($text)) {
	                    		$text_without_spaces = \app\helpers\StringHelper::collapseWhitespace($text);
	                    		//echo $rule."\n";
	                    		return [
		                           // 'id' => $node->extract(['id'])[0],
		                            '_text' => \app\helpers\StringHelper::lowercase(trim($text_without_spaces)),
		                        ];
	                    	}
	                    	//return null;
	                    });
        			}// end loop rules
        		}// end loop crawler
        	}// end loop links
        }// end loop crawlers

      	return $contents;
	}

	/**
	 * [setContent reoder array on each data for url]
	 * @param [array] $contents [content for each url]
	 */
	public static function setContent($contents)
	{
		$data = [];
		foreach ($contents as $url => $values) {
			$data[$url] = [];
			foreach ($values as $link => $nodes) {
				$data[$url][$link] = [];
				for ($n=0; $n < sizeof($nodes); $n++) { 
					if (!empty($nodes[$n])) {
						for ($s=0; $s < sizeof($nodes[$n]) ; $s++) { 
							if (!is_null($nodes[$n][$s])) {
								$text = \app\helpers\StringHelper::stripTags($nodes[$n][$s]['_text']);
								$text = \app\helpers\StringHelper::collapseWhitespace($text);
								if (!in_array($text, $data[$url][$link]) && !\app\helpers\StringHelper::isEmpty($text)) {
									$data[$url][$link][] = $text;
								}
							}
						}
					}
				}
			}// end loop values
		}// end loop contents
		return $data;
	}

	public static function sendTextAnilysis($content,$link = null)
	{
		// filter stop words
		$data = [];
		// analisis if web page
		$analysis = [];
		// Create a tokenizer object to parse the book into a set of tokens
		$tokenizer = new \TextAnalysis\Tokenizers\GeneralTokenizer();
		// set tokens
		$tokens = $tokenizer->tokenize($content);
		if(count($tokens)){
			// set anilisis
			$freqDist = new \TextAnalysis\Analysis\FreqDist($tokens);
			//Get all words
			$allwords = $freqDist->getKeyValuesByFrequency();
			//Get the top 10 most used words in Tom Sawyer 
			$words = array_splice($allwords, 0, 50);
			// get all stop words spanish
			$stop_factory = StopWordFactory::get('stop-words_spanish_es.txt');
			$stopWord_es = new StopWordsFilter($stop_factory);
			// get alll words english
			$stop_factory_en = StopWordFactory::get('stop-words_english.txt');
			$stopWord_en = new StopWordsFilter($stop_factory_en);
			
			// limit from ten words
			$limit = 10;
			foreach ($words as $word => $value) {
				$word_remove = \app\helpers\StringHelper::replacingPeriodsCommasAndExclamationPoints($word);
				$word_remove_emoji = \app\helpers\StringHelper::remove_emoji($word_remove);
				$word_remove_tags = \app\helpers\StringHelper::stripTags($word_remove_emoji);
				if(!\app\helpers\StringHelper::isEmpty($word_remove_tags) && !is_numeric($word_remove_tags) && \app\helpers\StringHelper::isAscii($word_remove_tags)){
					$word_lower = \app\helpers\StringHelper::lowercase($word_remove_tags);
					if(!is_null($stopWord_en->transform($word_lower)) && !is_null($stopWord_es->transform($word_lower)) && count($data) < $limit){
						$word_encode = \yii\helpers\Html::encode($word_lower);
						$data[$word_encode] = $value;
						unset($word_encode);
					}
				}
			}
			$analysis = \app\helpers\StringHelper::sortDataAnalysis($data,$link);
		}
		return (is_null($link)) ? $data : $analysis;
		
	}
}


?>