<?php
namespace app\models\file;

use yii;
use \Filebase\Database;

class JsonFile {

	public $filebase;
	public $documentId;
	public $fileName;
	public $source;
	
	private $_path;
	private $_data;

	/**
	 * [isDocumentExist is document_file by id]
	 * @return boolean [description]
	 */
	public function isDocumentExist(){
		if (!$this->filebase->has($this->documentId)){ return false;}
		return true;
	}
	/**
	 * [thereIsDataInDocument true is a document_file content data]
	 * @return [type] [description]
	 */
	public function thereIsDataInDocument(){
		return (!empty($this->filebase->get($this->documentId)->field('data'))) ? true : false;
	}
	/**
	 * [getSinceIdByProducts deprec]
	 * @param  array  $products [description]
	 * @return [type]           [description]
	 */
	public function getSinceIdByProducts($products = []){
		
		$products_sinceId = [];
		for($p = 0; $p < sizeOf($products); $p++){
			$ids = $this->filebase->get($this->documentId)->filter('data',$products[$p],function($tweet, $product) {
			    if($tweet['product_name'] == $product){
			    	return $tweet['id'];
			    }
			});
			// if empty($ids)
			if(empty($ids)){$products_sinceId[$products[$p]] = ''; }
			if(!empty($ids)){$products_sinceId[$products[$p]] = max($ids); }
			
		}
		return $products_sinceId;
	}
	/**
	 * [load set data in to _data]
	 * @param  [type] $data [description]
	 * @return [type]       [description]
	 */
	public function load($data){
		$this->_data[] =  $data;
	}
	/**
	 * [save set save]
	 * @return [type] [description]
	 */
	public function save(){
		if(!empty($this->_data)){
			if(!$this->fileName){
				$this->fileName = time();
			}
			$file = $this->filebase->get($this->fileName);
			foreach ($this->_data as $key => $value) {
	            $file->$key = $value;
	        }
	        $file->save();
		}
	}

	public function count(){
		return $this->filebase->count();
	}

	public function findAll(){
		$data = $this->filebase->findAll(true, true);
		$this->filebase->flushCache(); 
		return $data;
	}


	function __construct($documentId,$source)
	{
		$s = DIRECTORY_SEPARATOR;
		$this->documentId = $documentId;
		$this->source = $source;
		// path to folder flat archives
		$this->filebase = new Database([
			'dir'           => \Yii::getAlias('@data')."{$s}{$this->documentId}{$s}{$this->source}",
			'cache'         => true,
			'cache_expires' => 1800,
			'pretty'        => true,
			'safe_filename' => true,
	    	//'read_only'      => $read_only,
        ]);
	}

}

?>