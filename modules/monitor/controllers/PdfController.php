<?php

namespace app\modules\monitor\controllers;

use yii\helpers\Url;
use yii\web\NotFoundHttpException;
use Box\Spout\Writer\Common\Creator\WriterFactory;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Box\Spout\Common\Entity\Row;
use Box\Spout\Common\Type;

class PdfController extends \yii\web\Controller
{
	public $enableCsrfValidation = false;

    /**
     * Generate document pdf for Alert
     * @return Array data and url document
     */
    public function actionDocument($alertId)
    {
        
        $model = $this->findModel($alertId);
        $file_name  =  \app\helpers\PdfHelper::setName($model);
        
        $pathFolder = \Yii::getAlias("@pdf/{$alertId}");

        if(is_dir($pathFolder)){
            $files = \yii\helpers\FileHelper::findFiles($pathFolder,['only'=>['*.pdf']]);
            if(isset($files[0])){
                $filePath = $pathFolder."/{$file_name}";
                copy($files[0],"{$filePath}");
                \Yii::$app->response->sendFile($filePath)->send();
                unlink($filePath);
                return null;
            }
        }

        $filePath = $pathFolder."/{$file_name}";
        // load images
        $url_logo_small = \yii\helpers\Url::to('@web/img/logo_small.png',true);
        $url_logo = \yii\helpers\Url::to('@web/img/logo.png',true);
        // resources social data
        $resourcesSocialData = \app\helpers\PdfHelper::getDataForPdf($model); 
        
        if(count($resourcesSocialData)){
            // create folder
            $path = \app\helpers\DirectoryHelper::setFolderPath([
                'name' => $alertId,
                'path' => '@pdf',
            ]);
            // render partial html
            $html = $this->renderPartial('//document/_document',[
                'model' => $model,
                'resourcesSocialData' => $resourcesSocialData,
                'url_logo_small' => $url_logo_small,
                'url_logo' =>$url_logo,
            ]);
            set_time_limit(300);
            $pdf = \app\helpers\PdfHelper::getKartikMpdf($filePath,$html,$model);

            $pdf->render(); 
            \Yii::$app->response->sendFile($filePath)->send();
            unlink($filePath);
            unset($pdf);
            return null;
        }
        
    }



    public function actionDesing($alertId){
        // load model alert
        $model = \app\models\Alerts::findOne($alertId);
        // load images
        $url_logo_small = \yii\helpers\Url::to('@web/img/logo_small.png',true);
        $url_logo = \yii\helpers\Url::to('@web/img/logo.png',true);
        $resourcesSocialData = $this->getSocialData($model); 
        return $this->render('//document/_document',[
            'model' => $model,
            'resourcesSocialData' => $resourcesSocialData,
            'url_logo_small' => $url_logo_small,
            'url_logo' =>$url_logo,
        ]);
    }

    private function getSocialData($model){
        
        $alertResource = $this->getResourcesWithData($model);
        $data = [];
        if(count($alertResource['alertResource'])){
            $data = \app\helpers\PdfHelper::getGraphCountSourcesMentions($model,$alertResource);
            $data = \app\helpers\PdfHelper::getGraphResourceOnDate($model,$data);
            $data = \app\helpers\PdfHelper::getTermsFindByResources($model,$data);
            $data = \app\helpers\PdfHelper::getGraphDataTermsByResourceId($model,$data);
            $data =  \app\helpers\PdfHelper::getGraphCommonWordsByResourceId($model,$data);
            $data =  \app\helpers\PdfHelper::getMentionsByResourceId($model,$data);
        }
        return $data;
    }

    private function getResourcesWithData($model){
        $data = [];
        foreach($model->config->configSources as $source){
            if(\app\helpers\AlertMentionsHelper::getCountAlertMentionsByResourceId($model->id,$source->alertResource->id)){
                $data['alertResource'][$source->alertResource->name] =  $source->alertResource->id;
            }
        }
        return $data;

    }
    

    private function getEmojisByAlertId($alertId){
        $emojis = \app\helpers\MentionsHelper::getEmojisList($alertId); 
        return array_slice($emojis, 0, 10);;
    }
    /**
     * Generate document Excel for Alert
     * @return Object response
     */
    public function actionExportMentionsExcel($alertId){
        
        $model = $this->findModel($alertId);
        $start_date = \Yii::$app->formatter->asDatetime($model->config->start_date,'yyyy-MM-dd');
        $end_date   = \Yii::$app->formatter->asDatetime($model->config->end_date,'yyyy-MM-dd');
        $name       = "{$model->name} {$start_date} to {$end_date} mentions"; 
        $file_name  =  \app\helpers\StringHelper::replacingSpacesWithUnderscores($name);
       
        $pathFolder = \Yii::getAlias('@runtime/export/').$alertId;
        if(is_dir($pathFolder)){
            $files = \yii\helpers\FileHelper::findFiles($pathFolder,['only'=>['*.xlsx','*.xls']]);
            if(isset($files[0])){
                $folderPath = \Yii::getAlias("@runtime/export/{$alertId}/");
                $filePath = $folderPath."{$file_name}.xlsx";
                copy($files[0],"{$folderPath}{$file_name}.xlsx");
            }else{
                $folderPath = \Yii::getAlias("@runtime/export/{$alertId}/");
                $filePath = $folderPath."{$file_name}.xlsx";
                $data = \app\helpers\MentionsHelper::getDataMentions($model->id);
                \app\helpers\DocumentHelper::createExcelDocumentForMentions($filePath,$data);
                
            }
        }else{
            // set path folder options
            $folderOptions = [
                'path' => \Yii::getAlias('@runtime/export/'),
                'name' => $alertId,
            ];
            // create folder
            $folderPath = \app\helpers\DirectoryHelper::setFolderPath($folderOptions);
            $folderPath = \Yii::getAlias("@runtime/export/{$alertId}/");
            $filePath = $folderPath."{$file_name}.xlsx";
            $data = \app\helpers\MentionsHelper::getDataMentions($model->id);
            \app\helpers\DocumentHelper::createExcelDocumentForMentions($filePath,$data);
        }
        \Yii::$app->response->sendFile($filePath)->send();
        unlink($filePath);
    }


    /**
     * Finds the Alerts model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Alerts the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = \app\models\Alerts::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    
}
