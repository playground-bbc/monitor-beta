<?php 
namespace app\models\api;
use Yii;
use yii\base\Model;
use yii\helpers\ArrayHelper;

use app\models\ProductsSeries;
use app\models\Products;
use app\models\ProductsModels;
use app\models\ProductsFamily;
use app\models\ProductCategories;
use app\models\CategoriesDictionary;

require_once Yii::getAlias('@vendor') . '/autoload.php'; // call google client

class DriveApi extends Model{

	private $_data;
    public $driveTitle; // used in form alert
	
    /**
     * [getDictionariesForTopic get titles ]
     * @param  string $value [description]
     * @return [type]        [description]
     */
    public function getDictionariesTitlesForTopic()
    {
        $spreadsheetId = $this->_getSpreadSheetId();
        $response = $this->_get($spreadsheetId);

        $topic_dictionaries = [];
        $aliasNames = [
            'diccionario',
            'diccionarios',
            'dictionary',
            'words',
            'Palabras',
            'palabras'
        ];

        for ($i = 0; $i < sizeof($response->sheets); $i++) {
            $title_dictionary = $response->sheets[$i]->properties->title;
            $id_dictionary = $response->sheets[$i]->properties->sheetId;
            if(\app\helpers\StringHelper::containsAny(strtolower($title_dictionary),$aliasNames)){
                $topic_dictionaries[$id_dictionary] = $title_dictionary;
                
            }
        }

        return $topic_dictionaries;


    }



    public function getDictionariesByIdsForTopic($sheetIds = [])
    {
        $spreadsheetId = $this->_getSpreadSheetId();
        $response = $this->_get($spreadsheetId);

        $topic_dictionaries = [];
        $index = 0;

        for ($i = 0; $i < sizeof($response->sheets); $i++) {
            if (in_array($response->sheets[$i]->properties->sheetId,$sheetIds)) {
                $topic_dictionaries[$index]['id'] = $response->sheets[$i]->properties->sheetId;
                $topic_dictionaries[$index]['name'] = $response->sheets[$i]->properties->title;
                $index++;
            }
        }

        return $topic_dictionaries;


    }

	public function getDictionaries(){
	    $sheetName = $this->_getTitleDocument('dictionaries');

	    return (count($sheetName)) ? $sheetName : null;

	}

	public function getContentDictionaryByTitle($sheetNames = [])
    {
        $service = $this->_getServices();

        $spreadsheetId = $this->_getSpreadSheetId();
        $response = $this->_get($spreadsheetId);

        $values = [];

        foreach ($sheetNames as $sheetName) {
            $response           = $service->spreadsheets_values->get($spreadsheetId, $sheetName);
            $values[$sheetName] = $response->getValues();
        }
        if (count($values)) {
            foreach ($values as $key => $words) {
                for ($i = 0; $i < sizeof($words); $i++) {
                    $values[$key][$i] = trim($words[$i][0]);
                }
            }
        }

        return (count($values)) ? $values : null;

    }

    public function getContentDocument()
    {
        $service = $this->_getServices();

        $sheetNames = $this->_getTitleDocument('products');
        $spreadsheetId = $this->_getSpreadSheetId();

        if(!$spreadsheetId){
            throw new \yii\web\NotFoundHttpException(Yii::t('app','cannot get the spreadsheetId ლ(ಠ_ಠლ)   '));
        }

        $values = [];

        foreach ($sheetNames as $id => $sheetName) {
            $response = $service->spreadsheets_values->get($spreadsheetId, $sheetName);
            $values[] = $response->getValues();
        }

        try {
            $this->SaveToDatabase($values);
        }catch (ErrorException $e){
            throw new \yii\web\NotFoundHttpException(Yii::t('app','houston we have a problem, problem in the drive document ლ(ಠ_ಠლ)   '));
        }

    }

    /**
     * [SaveToDatabase calls distins function to save in database]
     * @param [array] $values [all products from dictionaries]
     */
    public function SaveToDatabase($values)
    {
        for ($i = 0; $i < sizeof($values); $i++) {
            for ($j = 1; $j < sizeof($values[$i]); $j++) {
                $id         = $this->saveSeries(trim($values[$i][$j][0]));
                $familyId   = $this->saveFamily($id, trim($values[$i][$j][1]));
                $categoryId = $this->saveProductCategory($familyId, trim($values[$i][$j][2]));
                $productId  = $this->saveProduct($categoryId, trim($values[$i][$j][3]));
                $modelId    = $this->saveProductsModel($productId, trim($values[$i][$j][4]));
            }
        }

    }

    public function saveSeries($value)
    {
        $value = $this->delete_quotation_marks($value);
        $params = [
            'abbreviation_name' => $value,
        ];
        $model = ProductsSeries::findOne($params);
        if (is_null($model)) {
            $model                    = new ProductsSeries;
            $model->name              = $value;
            $model->abbreviation_name = $value;
            $model->save();
        }
        return $model->id;
    }

    public function saveFamily($serieId, $value)
    {
        $value = $this->delete_quotation_marks($value);

        $params = [
            'seriesId' => $serieId,
            'name'     => $value,
        ];
        $model = ProductsFamily::findOne($params);
        if (is_null($model)) {
            $model           = new ProductsFamily;
            $model->seriesId = $serieId;
            $model->name     = $value;
            $model->save();
        }
        return $model->id;
    }

    public function saveProductCategory($familyId, $value)
    {
        $value = $this->delete_quotation_marks($value);

        $params = [
            'products_familyId' => $familyId,
            'name'     => $value,
        ];
        $model = ProductCategories::find()->where($params)->one();
        if (is_null($model)) {
            $model                    = new ProductCategories;
            $model->products_familyId = $familyId;
            $model->name              = $value;
            $model->save();
        }
        return $model->id;
    }

    public function saveProduct($categoryId, $value)
    {   
        $value = $this->delete_quotation_marks($value);

        $params = [
            'categoryId' => $categoryId,
            'name'       => $value,
        ];
        $model = Products::findOne($params);
        if (is_null($model)) {
            $model             = new Products;
            $model->categoryId = $categoryId;
            $model->name       = $value;
            $model->save();
        }
        return $model->id;

    }

    public function saveProductsModel($productId, $value)
    {   
        $value = $this->delete_quotation_marks($value);

        $params = [
            'productId'    => $productId,
            'name' => $value,
        ];
        $model = ProductsModels::findOne($params);
        if (is_null($model)) {
            $model            = new ProductsModels;
            $model->productId = $productId;
            $model->name      = $value;
            $model->save();
            $modelId = $model->id;
        }
        return $model->id;
    }

    public function saveCategoriesDictionary($categoryTitles)
    {
        foreach ($categoryTitles as $category) {
            $model = CategoriesDictionary::findOne(['name' => $category]);
            if (is_null($model)) {
               $model = new CategoriesDictionary();
               $model->name = $category;
               $model->save();
            }
        }
    }


	private function _getClient()
    {
        $client = new \Google_Client();
        $http   = new \GuzzleHttp\Client([
            //'verify' => 'c:\cert\cacert.pem'
        ]);
        $client->setHttpClient($http);
        $client->setAuthConfig(Yii::getAlias('@credencials'));
        $client->setApplicationName('monitor-app');
        $client->setScopes(\Google_Service_Sheets::SPREADSHEETS_READONLY);

        return $client;

    }

    private function _getServices()
    {
        // Get the API client and construct the service object.
        $client  = $this->_getClient();
        $service = new \Google_Service_Sheets($client);
        return $service;
    }

    private function _getSpreadSheetId(){
        $spreadSheetId = (new \yii\db\Query())
            ->select('api_secret_key')
            ->from('credencials_api')
            ->where(['name_app' => 'monitor-drive'])
            ->all();
        if($spreadSheetId)
            $spreadSheetId = ArrayHelper::getColumn($spreadSheetId,'api_secret_key')[0]; 


        return ($spreadSheetId != '') ? $spreadSheetId : false;       
    }



    private function _getTitleDocument($typeDocument){
        
        $spreadsheetId = $this->_getSpreadSheetId();
        $response = $this->_get($spreadsheetId);

        $sheetName = [];
        $aliasNames = [
            'diccionario',
            'diccionarios',
            'dictionary',
            'words'
        ];

        if($typeDocument == 'dictionaries'){
            for ($i = 0; $i < sizeof($response->sheets); $i++) {
                $title_dictionary = $response->sheets[$i]->properties->title;
                if(\app\helpers\StringHelper::containsAny(strtolower($title_dictionary),$aliasNames)){
                    $sheetName[$title_dictionary] = $title_dictionary;
                    
                }
            }

        }

        if($typeDocument == 'products'){
            for ($i = 0; $i < sizeof($response->sheets); $i++) {
                $title_dictionary = $response->sheets[$i]->properties->title;
                if(!in_array(strtolower($title_dictionary), $aliasNames)){
                    $sheetName[] = $title_dictionary;
                    
                }
            }

        }
        return $sheetName;

    }

    private function _get($spreadsheetId){
        // Get the API client and construct the service object.
        $service = $this->_getServices();
        try {
            $response = $service->spreadsheets->get($spreadsheetId);
        }catch (\GuzzleHttp\Exception\ConnectException $e){
            throw new \yii\web\NotFoundHttpException(Yii::t('app','houston we have a problem, problem in the drive document ლ(ಠ_ಠლ)   '));
        }
        return $response;
    }

    public function delete_quotation_marks($string) { 
        $result = str_replace(array('\'', '"'), '', $string);
        return $result; 
    } 
	


}

?>