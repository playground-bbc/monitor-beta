<?php
namespace app\helpers;

use yii;

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
						foreach ($links as $link) {
						    $link_web = $link->getURI();
						    $link_same_domain = \app\helpers\StringHelper::getDomain($link_web);
						    if($domain == $link_same_domain){
						      $all_links[] = $link_web;  
						    }
						    
						} // for each links
						
						
						// put original url
						if (!in_array($url, $all_links)) {
							array_push($all_links, $url);
						}
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

		/*$urls = [
			'http://testing-ground.scraping.pro/whoami'=>[
				'domain' => 'scraping.pro',
				'links'  => [
					'http://testing-ground.scraping.pro/whoami',
					'http://testing-ground.scraping.pro/textlist'
				],
			]
		];*/

		return $urls;
		
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

        if (!empty($urls)) {
            foreach ($urls as $url => $values) {
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
		                            '_text' => trim($text_without_spaces),
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

	public static function sendTextAnilysis($multipartForm,$link)
	{
		$client = new \GuzzleHttp\Client();
		$response = $client->request('POST', 'http://textalyser.net/index.php?lang=en#analysis',$multipartForm);

		$code = $response->getStatusCode();
		$reason = $response->getReasonPhrase();

		if ($code == 200 && $reason == 'OK') {
			$body = $response->getBody()->getContents();
        	$crawler = new \Symfony\Component\DomCrawler\Crawler($body);
        	// get table
        	$table = $crawler->filter('table')->eq(11);
        	// read the table
	        $tds = [];
	        foreach ($table as $node => $content) {
	            // create crawler instance for result
	            $crawler = new \Symfony\Component\DomCrawler\Crawler($content);
	            //iterate again
	            $index = 0;
	            $rows= [];
	            foreach ($crawler->filter('td') as $node) {
	                $rows[] = $node->nodeValue;
	                if (sizeof($rows) % 4 == 0) {
	                    $tds[] = $rows;
	                    $rows =[];
	                }
	            }
	        }
		}
		$analysis = \app\helpers\StringHelper::sortDataAnalysisTable($tds,$link);
		$analysis = self::removeStopWords($analysis);
		return $analysis;
		
	}

	public static function composeMultipartForm($contentGroup,$stoplist_perso = [])
	{
		$stoplist_perso = (!empty($stoplist_perso)) ? implode(" ", $stoplist_perso) : '';
		return [
            'multipart' => [
                [
                    'name'     => 'text_main',
                    'contents' => $contentGroup
                ],
                [
                    'name'     => 'site_to_analyze',
                    'contents' => 'http://'
                ],
                [
                    'name'     => 'file_to_analyze',
                    'contents' => '(binary)'
                ],
                [
                    'name'     => 'min_char',
                    'contents' => 3
                ],
                [
                    'name'     => 'special_word',
                    'contents' => ''
                ],
                [
                    'name'     => 'words_toanalyse',
                    'contents' => 10
                ],
                [
                    'name'     => 'count_numbers',
                    'contents' => 1
                ],
                [
                    'name'     => 'is_log',
                    'contents' => 1
                ],
                [
                    'name'     => 'stoplist_lang',
                    'contents' => 1
                ],
                [
                    'name'     => $stoplist_perso,
                    'contents' => 'worth year',
                ],
            ]
        ];
	}

	public static function removeStopWords($analysis = [])
	{
		if (!empty($analysis)) {
			$stoplist = self::getStopList();
			for ($a=0; $a < sizeof($analysis) ; $a++) { 
				if (in_array($analysis[$a]['name'], $stoplist)) {
					unset($analysis[$a]);
				}
			}
		}
		$analysis = array_values($analysis);
		return $analysis;
	}

	public static function getStopList()
	{
		return [
			//English
			'you',
			'I',
			'we',
			'your',
			'they',
			'it',
			'captcha',
			//spanish
			'que',
			'hay',
			'luego',
			'nada',
			'ser',
			'luego',
			'sin',
			'con',
			'nos',
			'el',
			'la',
			'las',
			'los',
			'yo',
			'tu',
			'ella',
			'ellas',
			'ellos',
			'nosotros',
			'que',
			'como',
			'por',
			'para',


		];
	}
}


?>