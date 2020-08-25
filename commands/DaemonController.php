<?php

namespace app\commands;

use yii\console\ExitCode;
use yii\console\Controller;
use Yii\helpers\ArrayHelper;
use yii\helpers\Console;

use app\models\Alerts;
use app\models\api\BaseApi;
use app\models\api\DriveApi;

use app\models\file\JsonFile;


/**
 * This command echoes the first argument that you have entered.
 *
 * This command will runs all the alerts.
 *
 * @author Eduardo Morales <eduardo@montana-studio.com>
 */
class DaemonController extends Controller
{
    /** run terminal ./yii daemon/alerts-run
     * [actionAlertsRun runs all alerts]
     * @return [type] [description]
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

    /** run terminal ./yii daemon/alerts-run-web
     * [actionAlertsRun runs all alerts when its resource are equal to web page]
     * @return [type] [description]
     */
    public function actionAlertsRunWeb(){
        
        //$startTime = microtime(true);
        
        $alert = new Alerts();
        $alertsConfig = $alert->getBringAllAlertsToRun(true,'PaginasWebs');
        
       // echo "Elapsed time on : getBringAllAlertsToRun ". (microtime(true) - $startTime) ." seconds \n";
        
        if(!empty($alertsConfig)){
           $baseApi = new BaseApi();
           $api = $baseApi->callResourcesApi($alertsConfig);
        }
       // echo "Elapsed time finis : actionAlertsRunWeb ". (microtime(true) - $startTime) ." seconds \n";
        return ExitCode::OK;
    }
    /** run terminal ./yii daemon/data-search
     * [actionDataSearch get json in transformed the data to db [Not finish]]
     * @return [type] [description]
     */
    public function actionDataSearch(){
        $alert = new Alerts();
        $alertsConfig = $alert->getBringAllAlertsToRun();
        // look in the folder
        if(!empty($alertsConfig)){
            $baseApi = new BaseApi();
            $api = $baseApi->readDataResource($alertsConfig);
            // send email
            \app\helpers\EmailHelper::sendCommonWords($alertsConfig);
        }
    }
    /**
     *  run terminal ./yii daemon/sync-products
     * [actionSyncProducts sync products to drive documents]
     * @return [type] [description]
     */
    public function actionSyncProducts(){
        $drive = new DriveApi();
        $drive->getContentDocument();
        return ExitCode::OK;
    }

    /**
     *  run terminal ./yii daemon/sync-dictionaries
     * [actionSyncProducts sync products to drive documents]
     * @return [type] [description]
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
     * run terminal ./yii daemon/insights-run
     * [actionInsightsRun call api to get insights]
     * @return [type] [description]
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
     * run terminal ./yii daemon/topic-run
     * [actionTopicRun console method to topic  search]
     * @param  string $resourceName [description]
     * @return [type]               [description]
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
     * [only development function]
     * @return [type] [description]
     */
    public function actionTruncateProducts(){
        \Yii::$app->db->createCommand()->delete('products_series','status = :status', [':status' => 1])->execute();
    }

}
