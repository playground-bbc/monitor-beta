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

    public function actionDocument()
    {
    	\Yii::$app->response->format = \yii\web\Response:: FORMAT_JSON;
    	// get data post
    	$data_post = json_decode(\Yii::$app->request->getRawBody());
    	// asign data
    	$alertId = $data_post->alertId;
    	$chart_bar_resources_count = $data_post->chart_bar_resources_count;
        $post_mentions = (isset($data_post->post_mentions)) ? $data_post->post_mentions : false;
        $products_interations = $data_post->products_interations;
        $date_resources = $data_post->date_resources;
    	// load images
    	$url_logo_small = \yii\helpers\Url::to('@img/logo_small.png');
        $url_logo = \yii\helpers\Url::to('@img/logo.jpg');
        // load model alert
        $model = \app\models\Alerts::findOne($alertId);
        // name file
        $start_date = \Yii::$app->formatter->asDatetime($model->config->start_date,'yyyy-MM-dd');
        $end_date   = \Yii::$app->formatter->asDatetime($model->config->end_date,'yyyy-MM-dd');
        $name       = "{$model->name} {$start_date} to {$end_date}.pdf"; 
        $file_name  =  \app\helpers\StringHelper::replacingSpacesWithUnderscores($name);
        // 
        // create option folder
        $folderOptions = [
            'name' => $alertId,
            'path' => '@pdf',
        ];
        // create folder
        $path = \app\helpers\DirectoryHelper::setFolderPath($folderOptions);
        // options pdf
        $options = new Options();
        $options->set('defaultFont', 'Courier');
        $options->set('isRemoteEnabled', TRUE);
        $options->set('debugKeepTemp', TRUE);
        $options->set('isHtml5ParserEnabled', false);
        // pdf libraries
        $pdf = new Dompdf();
        $pdf->set_option("isPhpEnabled", true);
        // render partial html
        $html = $this->renderPartial('_document',[
            'model' => $model,
            'url_logo' => $url_logo,
            'post_mentions' => $post_mentions,
            'date_resources' => $date_resources,
            'url_logo_small' => $url_logo_small,
            'products_interations' => $products_interations,
            'chart_bar_resources_count' => $chart_bar_resources_count
        ]);
        // load html
        $pdf->load_html($html);
        $pdf->render();
        ob_end_clean();

        // move file
        file_put_contents( $path.$file_name, $pdf->output()); 
        
        $url = Url::to('@web/pdf/'.$model->id.'/'.$file_name);
        return array('data' => $url,'filename' => $file_name); 
    }

    public function actionExportMentionsExcel($alertId){

        $writer = WriterEntityFactory::createXLSXWriter();
        $model = \app\models\Alerts::findOne($alertId);
        $start_date = \Yii::$app->formatter->asDatetime($model->config->start_date,'yyyy-MM-dd');
        $end_date   = \Yii::$app->formatter->asDatetime($model->config->end_date,'yyyy-MM-dd');
        $name       = "{$model->name} {$start_date} to {$end_date} mentions"; 
        $file_name  =  \app\helpers\StringHelper::replacingSpacesWithUnderscores($name);
        $filePath = \Yii::getAlias('@runtime/export/')."{$file_name}.xlsx";
        $writer->openToFile($filePath); // write data to a file or to a PHP stream
        
        $cells = [
            WriterEntityFactory::createCell('Recurso Social'),
            WriterEntityFactory::createCell('Término buscado'),
            WriterEntityFactory::createCell('Date created'),
            WriterEntityFactory::createCell('Name'),
            WriterEntityFactory::createCell('Username'),
            WriterEntityFactory::createCell('Title'),
            WriterEntityFactory::createCell('Mention'),
            WriterEntityFactory::createCell('url'),

        ];
        ini_set('max_execution_time', 600);
        $data = $this->getData($model->id);
        
        /** add a row at a time */
        $singleRow = WriterEntityFactory::createRow($cells);
        $writer->addRow($singleRow);
        
        
        /** Shortcut: add a row from an array of values */
        for ($v=0; $v < sizeOf($data) ; $v++) {
            $rowFromValues = WriterEntityFactory::createRowFromArray($data[$v]);
            $writer->addRow($rowFromValues);
        }
        
        $writer->close();
        
        \Yii::$app->response->sendFile($filePath)->send();
        unlink($filePath);
    }


    public function getData($alertId){
        
        $db = \Yii::$app->db;
        $duration = 60;
        
        $where['alertId'] = $alertId;
        if(isset($params['resourceId'])){
            $where['resourcesId'] = $params['resourceId'];
        }
        // if resourceId if not firts level on params
        if(isset($params['MentionSearch']['resourceId'])){
            $where['resourcesId'] = $params['MentionSearch']['resourceId'];
        }
       
        $alertMentions = $db->cache(function ($db) use ($where) {
          return (new \yii\db\Query())
            ->select('id')
            ->from('alerts_mencions')
            ->where($where)
            ->orderBy(['resourcesId' => 'ASC'])
            ->all();
        },$duration); 
        
        $alertsId = \yii\helpers\ArrayHelper::getColumn($alertMentions,'id');  
        
        $rows = (new \yii\db\Query())
        ->cache($duration)
        ->select([
          'recurso' => 'r.name',
          'term_searched' => 'a.term_searched',
          'created_time' => 'm.created_time',
          'name' => 'u.name',
          'screen_name' => 'u.screen_name',
          'subject' => 'm.subject',
          'message_markup' => 'm.message_markup',
          'url' => 'm.url',
        ])
        ->from('mentions m')
        ->where(['alert_mentionId' => $alertsId])
        ->join('JOIN','alerts_mencions a', 'm.alert_mentionId = a.id')
        ->join('JOIN','resources r', 'r.id = a.resourcesId')
        ->join('JOIN','users_mentions u', 'u.id = m.origin_id')
        ->orderBy(['m.created_time' => 'ASC']);
        //->all();
        $data = [];    
        if($rows){
            foreach($rows->batch() as $mentions){
                for ($r=0; $r < sizeOf($mentions) ; $r++) { 
                    if(isset($mentions[$r]['created_time'])){
                        $mentions[$r]['created_time'] =  \Yii::$app->formatter->asDate($mentions[$r]['created_time'], 'yyyy-MM-dd');
                    }
                    $data[] = $mentions[$r];
                }
            }
        }

        return $data;
    }
}
