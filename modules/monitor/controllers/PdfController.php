<?php

namespace app\modules\monitor\controllers;

use yii\helpers\Url;

use Dompdf\Dompdf;
use Dompdf\Options;
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
        set_time_limit(300);
    	// load images
    	$url_logo_small = \yii\helpers\Url::to('@web/img/logo_small.png',true);
        $url_logo = \yii\helpers\Url::to('@web/img/logo.png',true);
        // load model alert
        $model = \app\models\Alerts::findOne($alertId);
        // name file
        $start_date = \Yii::$app->formatter->asDatetime($model->config->start_date,'yyyy-MM-dd');
        $end_date   = \Yii::$app->formatter->asDatetime($model->config->end_date,'yyyy-MM-dd');
        $name       = "{$model->name} {$start_date} to {$end_date}.pdf"; 
        $file_name  =  \app\helpers\StringHelper::replacingSpacesWithUnderscores($name);
        // resources social data
        $resourcesSocialData = $this->getSocialData($model); 

        // create option folder
        $folderOptions = [
            'name' => $alertId,
            'path' => '@pdf',
        ];
        // create folder
        $path = \app\helpers\DirectoryHelper::setFolderPath($folderOptions);
        // options pdf
        $options = new Options();
        //$options->set('defaultFont', 'Courier');
        $options->set('isRemoteEnabled', true);
        $options->set('debugKeepTemp', true);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isPhpEnabled', true);
        // pdf libraries
        $pdf = new Dompdf($options);
        //$pdf->set_paper('letter', 'landscape');
        
        // render partial html
        $html = $this->renderPartial('_document',[
            'model' => $model,
            'url_logo' => $url_logo,
            'url_logo_small' => $url_logo_small,
            'resourcesSocialData' => $resourcesSocialData
        ]);
        // load html
        $pdf->load_html($html);
        $pdf->render();
        ob_end_clean();

        // move file
        file_put_contents( $path.$file_name, $pdf->output()); 
        
        //$url = Url::to('@web/pdf/'.$model->id.'/'.$file_name);
        //return array('data' => $url,'filename' => $file_name); 
    }


    private function getSocialData($model){
        
        $data = $this->getTermsFindByResources($model);
        $data = $this->getGraphDataTermsByResourceId($model,$data);
        return $data;
        
    }

    private function getTermsFindByResources($model){
        $resources = [];
        foreach($model->config->configSources as $source){
            if(\app\helpers\AlertMentionsHelper::getCountAlertMentionsByResourceId($model->id,$source->alertResource->id)){
                $termsFind = \app\helpers\MentionsHelper::getProductInteration($model->id,$source->alertResource->id);
                for($t = 0; $t < sizeOf($termsFind['data']); $t++){
                    $resources[$source->alertResource->name]['terms'][] =$termsFind['data'][$t][0];
                }
            }
        }
        return $resources;
    }

    private function getGraphDataTermsByResourceId($model,$data){

        foreach($model->config->configSources as $source){
            $url = \app\helpers\DocumentHelper::actionGraphDataTermsByResourceId($model->id,$source->alertResource->id);
            $data[$source->alertResource->name]['url_graph_data_terms'] = $url;
        }
        return $data;
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
