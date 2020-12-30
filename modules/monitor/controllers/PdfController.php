<?php

namespace app\modules\monitor\controllers;

use yii\helpers\Url;

use Box\Spout\Writer\Common\Creator\WriterFactory;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Box\Spout\Common\Entity\Row;
use Box\Spout\Common\Type;
use kartik\mpdf\Pdf;

class PdfController extends \yii\web\Controller
{
	public $enableCsrfValidation = false;

    /**
     * Generate document pdf for Alert
     * @return Array data and url document
     */
    public function actionDocument($alertId)
    {
        // load images
        $url_logo_small = \yii\helpers\Url::to('@web/img/logo_small.png',true);
        $url_logo = \yii\helpers\Url::to('@web/img/logo.png',true);
        // load model alert
        $model = \app\models\Alerts::findOne($alertId);
        // name file
        $file_name  =  \app\helpers\PdfHelper::setName($model);
        // emojis
        $emojis = $this->getEmojisByAlertId($model->id);
        // resources social data
        $resourcesSocialData = \app\helpers\PdfHelper::getDataForPdf($model); 
        
        if(count($resourcesSocialData)){
            // create folder
            $path = \app\helpers\DirectoryHelper::setFolderPath([
                'name' => $alertId,
                'path' => '@pdf',
            ]);
            //$this->layout = '';
            // render partial html
            $html = $this->renderPartial('_document',[
                'model' => $model,
                // 'emojis' => $emojis,
                'resourcesSocialData' => $resourcesSocialData,
                'url_logo_small' => $url_logo_small,
                'url_logo' =>$url_logo,
            ]);
            set_time_limit(300);
            $pdf = new \kartik\mpdf\Pdf([
                'filename' => $path.$file_name,
                // set to use core fonts only
                'mode' => Pdf::MODE_CORE, 
                // A4 paper format
                'format' => Pdf::FORMAT_A4, 
                // portrait orientation
                'orientation' => Pdf::ORIENT_PORTRAIT, 
                // stream to browser inline
                'destination' => Pdf::DEST_FILE, 
                // your html content input
                'content' => $html,  
                // format content from your own css file if needed or use the
                // enhanced bootstrap css built by Krajee for mPDF formatting 
                'cssFile' => '@vendor/kartik-v/yii2-mpdf/src/assets/kv-mpdf-bootstrap.min.css',
                // any css to be embedded if required
                'cssInline' => '.kv-heading-1{font-size:18px}', 
                // set mPDF properties on the fly
                'options' => ['title' => $model->name],
                // call mPDF methods on the fly
                'methods' => [ 
                    'SetHeader'=>[$model->name], 
                    'SetFooter'=>['{PAGENO}'],
                ]
            ]);

            // return the pdf output as per the destination setting
            return $pdf->render(); 

        }
        
        //$url = Url::to('@web/pdf/'.$model->id.'/'.$file_name);
        //return array('data' => $url,'filename' => $file_name); 
    }



    public function actionDesing($alertId){
        // load model alert
        $model = \app\models\Alerts::findOne($alertId);
        // load images
        $url_logo_small = \yii\helpers\Url::to('@web/img/logo_small.png',true);
        $url_logo = \yii\helpers\Url::to('@web/img/logo.png',true);
        $resourcesSocialData = $this->getSocialData($model); 
        return $this->render('_desing',[
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

        
        $model = \app\models\Alerts::findOne($alertId);
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


    
}
