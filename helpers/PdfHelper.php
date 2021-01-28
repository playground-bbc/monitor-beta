<?php
namespace app\helpers;

use yii;
use kartik\mpdf\Pdf;
use Mpdf;

/**
 * PdfHelper wrapper for pdf.
 * @author Eduardo Morales <eduardo@montana-studio.com>
 * @group  Montana-Studio LG 
 */
class PdfHelper{

    /**
     * This create name to pdf compose by alert name + start date + end date.
     * @param Alerts $model form alert.
     * @return string file name
     */
    public static function setName($model){
        // name file
        $start_date = \Yii::$app->formatter->asDatetime($model->config->start_date,'yyyy-MM-dd');
        $end_date   = \Yii::$app->formatter->asDatetime($model->config->end_date,'yyyy-MM-dd');
        $name       = "{$model->name} {$start_date} to {$end_date}.pdf"; 
        $file_name  =  \app\helpers\StringHelper::replacingSpacesWithUnderscores($name);

        return $file_name;
    }

    /**
     * This create name to pdf compose by alert name + start date + end date.
     * @param string file_path form pdf.
     * @param string content html for pdf.
     * @param Alerts $model for alert.
     * @return Pdf 
     */
    public static function getKartikMpdf($file_path,$content,$model){

        $defaultConfig = (new Mpdf\Config\ConfigVariables())->getDefaults();
        $fontDirs = $defaultConfig['fontDir'];

        $defaultFontConfig = (new Mpdf\Config\FontVariables())->getDefaults();
        $fontData = $defaultFontConfig['fontdata'];

        
        return new \kartik\mpdf\Pdf([
            'filename' => $file_path,
            // set to use core fonts only
            'mode' => Pdf::MODE_UTF8, 
            // A4 paper format
            'format' => Pdf::FORMAT_A4, 
            // portrait orientation
            'orientation' => Pdf::ORIENT_PORTRAIT, 
            // stream to browser inline
            'destination' => Pdf::DEST_FILE, 
            // your html content input
            'content' => $content,  
            // format content from your own css file if needed or use the
            // enhanced bootstrap css built by Krajee for mPDF formatting 
            'cssFile' => '@vendor/kartik-v/yii2-mpdf/src/assets/kv-mpdf-bootstrap.min.css',
            //'defaultFont' => 'noto-color-emoji',
            // any css to be embedded if required
            'cssInline' => ".emoji{ width:30.5%; font-family:noto-emoji;font-size:22px;} .list-inline{list-style: none;float: left;}", 
            // set mPDF properties on the fly
            'options' => [
                'title' => $model->name,
                'fontDir' => array_merge($fontDirs, [
                    \yii ::getalias ("@webroot/fonts")
                ]),
                'fontdata' => $fontData + [
                    'noto-emoji' => [
                        'R' => 'NotoEmoji-661A.ttf',
                        
                    ]
                ],
            ],
            // call mPDF methods on the fly
            'methods' => [ 
                'SetHeader'=>[$model->name], 
                'SetFooter'=>['{PAGENO}'],
            ]
        ]);
    }

    /**
     * getDataForPdf [ calls function for data on pdf]
     * @param Alerts $model for alert.
     * @return array 
     */
    public static function getDataForPdf($model){

        $data = [];
        foreach($model->config->configSources as $source){
            if(\app\helpers\AlertMentionsHelper::getCountAlertMentionsByResourceId($model->id,$source->alertResource->id)){
                $data['alertResource'][$source->alertResource->name] =  $source->alertResource->id;
            }
        }

        if(count($data['alertResource'])){
            $data = \app\helpers\PdfHelper::getGraphCountSourcesMentions($model,$data);
            $data = \app\helpers\PdfHelper::getGraphResourceOnDate($model,$data);
            $data = \app\helpers\PdfHelper::getEmojis($model,$data);
            $data = \app\helpers\PdfHelper::getTermsFindByResources($model,$data);
            $data = \app\helpers\PdfHelper::getGraphDataTermsByResourceId($model,$data);
            $data = \app\helpers\PdfHelper::getGraphDomainsByResourceId($model,$data);
            $data =  \app\helpers\PdfHelper::getGraphCommonWordsByResourceId($model,$data);
            $data =  \app\helpers\PdfHelper::getMentionsByResourceId($model,$data);
        }
        return $data;

    }

    /**
     * getGraphCountSourcesMentions [ calls function for grahp count sources on mentions]
     * @param Alerts $model for alert.
     * @param array $alertResource
     * @return array 
     */
    public static function getGraphCountSourcesMentions($model,$alertResource){
        $url = \app\helpers\DocumentHelper::GraphCountSourcesMentions($model->id);
        $alertResource['url_graph_count_sources'] = $url;
        return $alertResource;
    }

    /**
     * getGraphResourceOnDate [ calls function for grahp count sources order by dates]
     * @param Alerts $model for alert.
     * @param array $alertResource
     * @return array 
     */
    public static function getGraphResourceOnDate($model,$alertResource){
        $url = \app\helpers\DocumentHelper::GraphResourceOnDate($model->id);
        if(!is_null($url)){
            $alertResource['url_graph_date_sources'] = $url;
        }
        return $alertResource;
    }

    /**
     * getEmojis [ calls function for grahp count getEmojis]
     * @param Alerts $model for alert.
     * @param array $alertResource
     * @return array 
     */
    public static function getEmojis($model,$alertResource){
        $emojis = \app\helpers\MentionsHelper::getEmojisListPointHex($model->id);
        if(count($emojis['data'])){
            $alertResource['emojis'] = $emojis['data'];
        }
        return $alertResource;
    }

    /**
     * getTermsFindByResources [ return array terms/products find by resource]
     * @param Alerts $model for alert.
     * @param array $alertResource
     * @return array 
     */
    public static function getTermsFindByResources($model,$alertResource){
        
        foreach($alertResource['alertResource'] as $resourceName => $resourceId){
            $termsFind = \app\helpers\MentionsHelper::getProductInteration($model->id,$resourceId);
            for($t = 0; $t < sizeOf($termsFind['data']); $t++){
                $alertResource['resources'][$resourceName]['terms'][] =$termsFind['data'][$t][0];
            }
        }
        return $alertResource;
    }

    /**
     * getGraphDataTermsByResourceId [ return array terms/products find by resource]
     * @param Alerts $model for alert.
     * @param array $alertResource
     * @return array 
     */
    public static function getGraphDataTermsByResourceId($model,$alertResource){

        foreach($alertResource['alertResource'] as $resourceName => $resourceId){
            $url = \app\helpers\DocumentHelper::actionGraphDataTermsByResourceId($model->id,$resourceId);
            $alertResource['resources'][$resourceName]['url_graph_data_terms'] = $url;
            
        }
        return $alertResource;
    }

    /**
     * getGraphDomainsByResourceId [ return graph domains by resource id]
     * @param Alerts $model for alert.
     * @param array $alertResource
     * @return array 
     */
    public static function getGraphDomainsByResourceId($model,$alertResource){

        foreach($alertResource['alertResource'] as $resourceName => $resourceId){
            $url = \app\helpers\DocumentHelper::actionGraphDomainsByResourceId($model->id,$resourceId);
            if(!is_null($url)){
                $alertResource['resources'][$resourceName]['url_graph_domains'] = $url;
            }
            
        }
        return $alertResource;
    }

    /**
     * getGraphCommonWordsByResourceId [ return graph common words by resource id]
     * @param Alerts $model for alert.
     * @param array $alertResource
     * @return array 
     */
    public static function getGraphCommonWordsByResourceId($model,$alertResource){

        foreach($alertResource['alertResource'] as $resourceName => $resourceId){
            $url = \app\helpers\DocumentHelper::GraphCommonWordsByResourceId($model->id,$resourceId);
            if(!is_null($url)){
                $alertResource['resources'][$resourceName]['url_graph_common_words'] = $url;
            }
        }
        return $alertResource;
    }

    /**
     * getMentionsByResourceId [ return mentions by resourceId]
     * @param Alerts $model for alert.
     * @param array $alertResource
     * @return array 
     */
    public static function getMentionsByResourceId($model,$alertResource){
        
        $searchModel = new  \app\models\grid\MentionSearch();
        foreach($alertResource['alertResource'] as $resourceName => $resourceId){
            $data = $searchModel->getData(['resourceId' => $resourceId,'limits' => 10],$model->id);
            if(!is_null($data) && count($data)){
                $provider = new \yii\data\ArrayDataProvider([
                    'allModels' => $data,
                ]);
                $alertResource['resources'][$resourceName]['provider'] = $provider;
            }
        }
        return $alertResource;
    }
}