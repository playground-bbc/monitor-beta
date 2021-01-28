<?php

namespace app\models\search;

use yii\helpers\ArrayHelper;
use yii\db\Command;
/**
 * ExcelSearch represents the model behind search products, dictionaries and save`.
 */
class ExcelSearch {

	public $alertId;
    public $data = [];
    public $products;
    public $resourcesId;
    public $isDictionaries = false;
    public $isBoolean = false;

    public $start_date;
    public $end_date;

	/**
     * [load load in to local variables]
     * @param  [array] $params [product [tweets]]
     * @return [boolean]
     */
    public function load($params){
        if(empty($params)){
           return false;     
        }

        $this->alertId = ArrayHelper::getValue($params, 0);
        $this->isDictionaries = \app\helpers\AlertMentionsHelper::isAlertHaveDictionaries($this->alertId);
        $this->products = $this->getProducts();
        $this->resourcesId = \app\helpers\AlertMentionsHelper::getResourceIdByName('Excel Document');

        $this->_setDateAlerts();


        // is boolean

        // loop data
        for($p = 1; $p < sizeof($params); $p++){
            // loop with json file
            for($j = 0; $j < sizeof($params[$p]); $j++){
                // loop in data file
                foreach($params[$p] as $data => $paginations){
                	foreach ($paginations as $pagination => $values){
                		for($v = 0; $v < sizeof($values); $v++){
                            if(!is_null($values[$v]['Post Snippet'])){
                                $message_markup =  \app\helpers\StringHelper::removeNonAscii($values[$v]['Post Snippet']);
                                $values[$v]['message_markup'] = \app\helpers\StringHelper::replaceAccents($message_markup);
                                $this->data[] = $values[$v];

                            }
                			
                		}
                	}
                }
            } // end loop json
        }
        // looking products
        $this->searchProductsData();
        // filter date
        $this->filterData();
        // save products found it
        $this->saveProductsMentionsAlerts();
        // finish search
        $this->searchFinish();
        
        return true;
    }



    /**
     * search products in the data
     */
    private function searchProductsData(){
    	$data = [];
    	$data_count = count($this->data);

    	for($d = 0; $d < sizeof($this->data); $d++){
    		$title = $this->data[$d]['Title'];
    		$message_markup = $this->data[$d]['message_markup'];
    		for($p = 0; $p < sizeof($this->products); $p++){
    			// destrutura el product
				$product_data = \app\helpers\StringHelper::structure_product_to_search($this->products[$p]);
    			if($data_count){

    				if(!is_null($title)){
        				$is_contains = (count($product_data) > 3) ? \app\helpers\StringHelper::containsAny($title,$product_data) : \app\helpers\StringHelper::containsAll($title,$product_data);

        				if($is_contains){
        					// if a not key
    						if(!ArrayHelper::keyExists($this->products[$p], $data, false)){
    							$data[$this->products[$p]] = [] ;

    						}
    						// if a not in array
    						if(!in_array($this->data[$d],$data[$this->products[$p]])){
    							$data[$this->products[$p]][] = $this->data[$d];
    							$data_count --;
    							break;

    						}

        				}

    			     }


        			if(!is_null($message_markup)){
        				$is_contains = (count($product_data) > 3) ? \app\helpers\StringHelper::containsAny($message_markup,$product_data) : \app\helpers\StringHelper::containsAll($message_markup,$product_data);

        				if($is_contains){
        					// if a not key
    						if(!ArrayHelper::keyExists($this->products[$p], $data, false)){
    							$data[$this->products[$p]] = [] ;

    						}
    						// if a not in array
    						if(!in_array($this->data[$d],$data[$this->products[$p]])){
    							$data[$this->products[$p]][] = $this->data[$d];
    							$data_count --;
    							break;

    						}

        				}

        			}

    		    }

    		}// end loop products
    	}// end loop data
        
    	$this->data = $data;

    }

    /**
     * get products in the alert
     */
    private function getProducts(){
    	$products_models_alerts = \app\models\ProductsModelsAlerts::findAll(['alertId' => $this->alertId]);
    	$alertsConfig = [];
        
        if(!empty($products_models_alerts)){
            $alertsConfig['products'] = [];
            foreach($products_models_alerts as $product){
                // models
                if(!in_array($product->productModel->name,$alertsConfig['products'])){
                    array_push($alertsConfig['products'], $product->productModel->name);
                }
                // products
                if(!in_array($product->productModel->product->name,$alertsConfig['products'])){
                    array_push($alertsConfig['products'], $product->productModel->product->name);
                }
                // category
                if(!in_array($product->productModel->product->category->name,$alertsConfig['products'])){
                    array_push($alertsConfig['products'], $product->productModel->product->category->name);
                }
               // array_push($alertsConfig[$c]['products'], $product->productModel->product->category->productsFamily->name);
            }
        }
        // order products by his  length
		array_multisort(array_map('strlen', $alertsConfig['products']), $alertsConfig['products']);
		
		return $alertsConfig['products'];
        
    }

    private function filterData(){
        $model = [];

        foreach($this->data as $product => $data){
            for($d = 0; $d < sizeof($data); $d++){
                //$mention_date = \Yii::$app->formatter->asDatetime($data[$d]['Mention Date'],'yyyy-MM-dd');
                $mention_date = $data[$d]['Mention Date'];
                if(\app\helpers\DateHelper::isBetweenDate($mention_date,$this->start_date,$this->end_date)){
                    $model[$product][] = $this->data[$product][$d];

                }

            }

        }

        $this->data = $model;
    }

    private function saveProductsMentionsAlerts(){
    	$products = array_keys($this->data);
    	$where = [
            'alertId'     => $this->alertId,
            'resourcesId' => $this->resourcesId,
            'condition'   =>  'ACTIVE',
            'type'        =>  'document',

    	];


    	for($p = 0; $p < sizeof($products); $p++){

    		$isProductExist = \app\models\AlertsMencions::find()->where(['alertId' => $this->alertId, 'resourcesId' => $this->resourcesId,'term_searched' => $products[$p]])->exists();
    		if(!$isProductExist){
    			$model = new \app\models\AlertsMencions();
    			$model->alertId = $this->alertId;
    			$model->resourcesId = $this->resourcesId;
    			$model->condition = 'ACTIVE';
    			$model->type = 'document';
    			$model->term_searched = $products[$p];
    			$model->save();
    		}

    	}

    }



    /**
     * methodh applied depends of type search
     *
     *
     * @return boolean status
     */
    public function search()
    {   
        // if doesnt dictionaries and doesnt boolean
        if(!$this->isDictionaries && !$this->isBoolean){
            // echo "no dictionaries .. \n";
            // save all data
            $mentions = $this->data;
            $search = $this->saveMentions($mentions);
            return $search;
        }

        // if  dictionaries and  boolean
        if($this->isDictionaries && $this->isBoolean){
            // init search
            echo "boolean and dictionaries \n";
            // retur something
        }

        // if  dictionaries and  !boolean
        if($this->isDictionaries && !$this->isBoolean){
            // init search
           // echo "only dictionaries \n";
            $mentions = $this->data;
            $data = $this->searchDataByDictionary($mentions);
            $search = $this->saveMentions($data);
            return $search;
        }

        // if  !dictionaries and  boolean
        if(!$this->isDictionaries && $this->isBoolean){
            // init search
            echo "only boolean \n";
            // retur something
        }

    }

    /**
     * [searchDataByDictionary search looking by dictionaries]
     * @param  [type] $mentions [description]
     * @return [type]           [description]
     */
    private function searchDataByDictionary($mentions){
    	
    	$words = \app\helpers\AlertMentionsHelper::getDictionariesWords($this->alertId);
         
    	foreach ($mentions as $product => $mention){
    		for($m =  0; $m < sizeof($mention); $m++){
    			if(!is_null($mention[$m]['message_markup'])){
    				$wordsId = [];
                    for($w = 0; $w < sizeof($words); $w++){
                    	$sentence = $mentions[$product][$m]['message_markup'];
                        $word = " {$words[$w]['name']} ";
                        $containsCount = \app\helpers\StringHelper::containsCount($sentence, $word);
                        if($containsCount){
                        	$wordsId[$words[$w]['id']] = $containsCount;
                            $mentions[$product][$m]['message_markup']  = \app\helpers\StringHelper::replaceIncaseSensitive($sentence,$word,"<strong>{$word}</strong>");

                        }// end if containsCount

                    }// end loop words
                    if(!empty($wordsId)){
                        $mentions[$product][$m]['wordsId'] = $wordsId;
                    }else{
                        unset($mentions[$product][$m]);
                    }
                    array_values($mentions[$product]);
    			}// end if is_null

    		}// end loop mention 
    	}// end foreach mentions

    	return $mentions;
    }

    /**
     * [saveMentions save mentions or update in the database]
     * @param  [type] $mentions [description]
     * @return [type]           [description]
     */
    private function saveMentions($mentions){
        $error = [];
        foreach($mentions as $product => $data){
            $alertsMencions = $this->_findAlertsMencions($product);
            if(!is_null($alertsMencions)){
                foreach ($data as  $mention){
                    if(!empty($mention['Author Username']) && !empty($mention['Author Name']) && !is_numeric($mention['Author Username']) && !is_numeric($mention['Author Name']) ){
                        $user = $this->_saveUserMencions($mention);
                        if(!$user->errors){
                            $mention_model = $this->_saveMentions($mention,$alertsMencions->id,$user->id);
                            if(!$mention_model->errors){
                                if(ArrayHelper::keyExists('wordsId', $mention, false)){
                                    $wordIds = $mention['wordsId'];
                                    // save Keywords Mentions 
                                    $this->saveKeywordsMentions($wordIds,$mention_model->id);
                                }
                            }else{ $error['mention'] =  $mention_model->errors; }
                        }else{ $error['user'] =  $user->errors; }
                    }
                }
            }
        }
        if(empty($error)){
            $model = [
                'Excel Document' => [
                    'resourceId' => $this->resourcesId,
                    'status' => 'Finish'
                ]
            ];
            \app\helpers\HistorySearchHelper::createOrUpdate($this->alertId, $model);
        }

        return (empty($error)) ? true : false;

    }

    /**
     * Finds the AlertsMencions model based on product key value.
     * @param string $product
     * @return AlertsMencions the loaded model
     */
    private function _findAlertsMencions($product)
    {

        $alertsMencions =  \app\models\AlertsMencions::find()->where([
            'alertId'        => $this->alertId,
            'resourcesId'    =>  $this->resourcesId,
            //'condition'      =>  'ACTIVE',
            'type'           =>  'document',
            'term_searched'  =>  $product,
        ])
        ->select('id')->one();

        return $alertsMencions;

    }

    /**
     * [saveUserMencions save user ]
     * @return [type] [description]
     */
    private function _saveUserMencions($mention){

        $origin = \app\helpers\MentionsHelper::saveUserMencions(
            [
                'screen_name' => $mention['Author Username'],
                'name'        => $mention['Author Name'],

            ],
            [
                'screen_name' => $mention['Author Username'],
                'name'        => $mention['Author Name'],
            ]
        );

        return $origin;


    }
    /**
     * [_saveMentions save mention in db]
     * @param  [type] $mention  [description]
     * @param  [type] $alertId  [description]
     * @param  [type] $originId [description]
     * @return [type]           [description]
     */
    private function _saveMentions($mention,$alertId,$originId){

        $mention_data['plataforma'] = $mention['Plataforma'];
        $mention_data['source']     = $mention['Source'];
        $mention_data['sentiment']  = $mention['Sentiment'];
       
        $formate_date   = \DateTime::createFromFormat('d/m/Y H:i', $mention['Mention Date']);
        $mention_date   = date_format($formate_date, 'U');
       
       
        //$mention_date   = strtotime($mention['Mention Date']);
        $url            = (!empty($mention['Mention URL'])) ? $mention['Mention URL']: null;
        $domain_url     = (!is_null($url)) ? \app\helpers\StringHelper::getDomain($url): null;
        $message        = $mention['Post Snippet'];
        $message_markup = $mention['message_markup'];

        $mention = \app\helpers\MentionsHelper::saveMencions(
            [
                'alert_mentionId' => $alertId,
                'origin_id'       => $originId, // url is unique
                'created_time'    => $mention_date,
            ],
            [
                'created_time'   => $mention_date,
                'mention_data'   => $mention_data,
                'message'        => $message,
                'message_markup' => $message_markup,
                'url'            => $url,
                'domain_url'     => $domain_url,
            ]
        );
        return $mention;

    }

    /**
     * [saveKeywordsMentions save or update  KeywordsMentions]
     * @param  [array] $wordIds   [array wordId => total count in the sentece ]
     * @param  [int] $mentionId   [id mention]
     */
    private function saveKeywordsMentions($wordIds,$mentionId){

        if(\app\models\KeywordsMentions::find()->where(['mentionId'=> $mentionId])->exists()){
            \app\models\KeywordsMentions::deleteAll('mentionId = '.$mentionId);
        }

        foreach($wordIds as $idwords => $count){
            for($c = 0; $c < $count; $c++){
                $model = new \app\models\KeywordsMentions();
                $model->keywordId = $idwords;
                $model->mentionId = $mentionId;
                $model->save();
            }
        }

    }
    /**
     * [_isDictionaries is the alert hace dictionaries]
     * @return boolean [description]
     */
    private function _isDictionaries(){
        if(!is_null($this->alertId)){
            $keywords = \app\models\Keywords::find()->where(['alertId' => $this->alertId])->exists();
            return $keywords;
        }
        return false;
    }

    /**
	 * [_setResourceId return the id from resource]
	 */
	private function _setResourceId(){
		
		$socialId = (new \yii\db\Query())
		    ->select('id')
		    ->from('type_resources')
		    ->where(['name' => 'Document'])
		    ->one();
		
		
		$resourcesId = (new \yii\db\Query())
		    ->select('id')
		    ->from('resources')
		    ->where(['name' => 'Excel Document','resourcesId' => $socialId['id']])
		    ->all();
		

		return ArrayHelper::getColumn($resourcesId,'id')[0];    
	}

    private function _setDateAlerts(){
        $config = \app\models\AlertConfig::findOne(['alertId' => $this->alertId]);
        $this->start_date = $config->start_date;
        $this->end_date = $config->end_date;
    }

    private function searchFinish()
    {
        $alertsMencions = \app\models\AlertsMencions::find()->where([
            'alertId'       => $this->alertId,
            'resourcesId'   => $this->resourcesId,
            'type'          => 'document',
            //'condition'       => 'ACTIVE'
        ])->all(); 

        $model = [
            'Excel Document' => [
                'resourceId' => $this->resourcesId,
                'status' => 'Pending'
            ]
        ];

        if(count($alertsMencions)){
            foreach ($alertsMencions as $alertsMencion) {
                $alertsMencion->condition = 'INACTIVE';
                $alertsMencion->save();
            }
            $model['Excel Document']['status'] = 'Finish';

        }

        \app\helpers\HistorySearchHelper::createOrUpdate($this->alertId, $model);

    }


}