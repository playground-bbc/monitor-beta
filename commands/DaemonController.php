<?php

namespace app\commands;

use yii\console\ExitCode;
use yii\console\Controller;

use app\models\Alerts;
use app\models\api\BaseApi;
use app\models\api\DriveApi;

/**
 * This command will runs all the alerts - insights - topic - data-search -sync terms and dictionaries google docs .
 *
 * @author Eduardo Morales <eduardo@montana-studio.com>
 */
class DaemonController extends Controller
{
    /** 
     * @param string $resourceName [ ej: Facebook Comments, Twitter, etc ..]
     * [actionAlertsRun runs all alerts active]
     * @return int Exit code
     */
    public function actionAlertsRun($resourceName = ''){
        $alert = new Alerts();
        $alertsConfig = $alert->getBringAllAlertsToRun(true,$resourceName);
      
        if(!empty($alertsConfig)){
           $baseApi = new BaseApi();
           $api = $baseApi->callResourcesApi($alertsConfig);
        }
        
        return ExitCode::OK;
    }

    /**
     * [actionAlertsRun runs all alerts  (Scraping) when its resource are equal to web page]
     * @return int Exit code
     */
    public function actionAlertsRunWeb(){
        
        $alert = new Alerts();
        $alertsConfig = $alert->getBringAllAlertsToRun(true,'PaginasWebs');
       
        if(!empty($alertsConfig)){
           $baseApi = new BaseApi();
           $api = $baseApi->callResourcesApi($alertsConfig);
        }
        return ExitCode::OK;
    }
    /** 
     * [actionDataSearch get json file in transformed the data to db]
     * @return int Exit code
     */
    public function actionDataSearch(){
        $alert = new Alerts();
        $alertsConfig = $alert->getBringAllAlertsToRun();
        // look in the folder
        if(!empty($alertsConfig)){
            $baseApi = new BaseApi();
            $api = $baseApi->readDataResource($alertsConfig);
        }
        return ExitCode::OK;
    }

    /**
     *  
     * [actionSyncProducts sync products to drive documents]
     * @return int Exit code
     */
    public function actionSyncProducts(){
        $drive = new DriveApi();
        $drive->getContentDocument();
        return ExitCode::OK;
    }

    /**
     * [actionSyncProducts sync products to drive documents]
     * @return int Exit code
     */
    public function actionSyncDictionaries(){
        $drive = new DriveApi();
        $dictionariesNames = $drive->getDictionaries();
        $Dictionarieskeywords = $drive->getContentDictionaryByTitle($dictionariesNames);
        foreach($Dictionarieskeywords as $dictionariesName => $keywords){
            $dictionaryModel = \app\modules\wordlists\models\Dictionaries::findOne(['name' => $dictionariesName]);
            if(!is_null($dictionaryModel)){
                
                for ($k=0; $k < sizeOf($keywords) ; $k++) { 
                    $isKeywordExists = \app\modules\wordlists\models\Keywords::find()->where(['name' => $keywords[$k]])->exists();
                    if(!$isKeywordExists){
                        $keywordModel = new \app\modules\wordlists\models\Keywords();
                        $keywordModel->dictionaryId = $dictionaryModel->id;
                        $keywordModel->name = $keywords[$k];
                        if(!$keywordModel->save()){
                            var_dump($keywordModel->errors);
                        }
                    }
                }
            }
        }
        return ExitCode::OK;
    }
    
    /**
     * [actionInsightsRun call api insights facebook of current client]
     * @return int Exit code
     */
    public function actionInsightsRun(){
        $userFacebook = \app\helpers\FacebookHelper::getUserActiveFacebook();
        if (!empty($userFacebook)) {
            $baseApi = new BaseApi();
            $api = $baseApi->callInsights($userFacebook);
        }
        return ExitCode::OK;
    }
    /**
     * [actionTopicRun console method to topic search]
     * @param  string $resourceName   [ej:Twitter,Livechat]
     * @return int Exit code               [description]
     */
    public function actionTopicRun($resourceName = "Paginas Webs")
    {
        $topics = \app\helpers\TopicsHelper::getTopicsByResourceName($resourceName);
        if (!empty($topics)) {
            $topicBase = new \app\modules\topic\models\api\TopicBaseApi();
            $api = $topicBase->callResourcesApiTopic($topics);
        }

        return ExitCode::OK;
    }
    /**
     * run terminal ./yii daemon/truncate-prodcuts
     * [only development function delete products]
     * @return void
     */
    public function actionTruncateProducts(){
        \Yii::$app->db->createCommand()->delete('products_series','status = :status', [':status' => 1])->execute();
    }

}
