<?php
namespace app\modules\report\helpers;

use yii;
use yii\db\Expression;
use yii\helpers\Console;

use app\modules\presentation\models\Presentation;
use app\modules\presentation\models\Section;
use app\modules\presentation\models\Page;
use app\modules\presentation\models\PageElement;
use app\modules\presentation\models\SectionType;

require_once Yii::getAlias('@vendor') . '/autoload.php'; // call google client

/**
 *
 * @author Eduardo Morales <eduardo@montana-studio.com>
 * @group  Montana-Studio LG 
 */


class PresentationHelper{

    public static function getScope(){
        return  [
            \Google_Service_Slides::DRIVE,
            \Google_Service_Slides::DRIVE_FILE,
            \Google_Service_Slides::PRESENTATIONS,
            \Google_Service_Sheets::SPREADSHEETS_READONLY
        ];
    }
    public static function saveSection($requestType,$typeSectionId,$modelId){

        $modelSection = new Section;
        $modelSection->presentationId = $modelId;
        $modelSection->typeSection = $typeSectionId;
        $modelSection->head_title = $requestType['head_title'];
        return $modelSection;

    }

    public static function savePage($title,$sectionId){

        $modelPage = new Page;
        $modelPage->sectionId = $sectionId;
        $modelPage->title = $title;
        return $modelPage;

    }

    public static function saveDinamicPageElement($name,$value,$pageId){
        
        $pageElementModel = new PageElement();
        $pageElementModel->pageId = $pageId;
        $pageElementModel->name = $name;
        $pageElementModel->value = $value;
        $pageElementModel->save();
        return $pageElementModel;
    }

    public static function savePageElements($requestPageElement,$pageId){
       
        foreach ($requestPageElement as $indexElements => $element) {
            $pageElementModel = new PageElement();
            $pageElementModel->pageId = $pageId;
            $pageElementModel->name = $element['name'][0];
            $pageElementModel->value = $element['value'];
            if($pageElementModel->validate()){
                $pageElementModel->save();
            }else{
                print_r($pageElementModel->errors);
                die();
            }
        }
    }


    public static function getSheetNames($url){
        
        $sheetNames = [];

        $targets = [
            'SNS' => [
                'A10:E14','A2:D3'
            ],
            'Gráficos' => [
                'A32:G39','A44:G51'
            ],
            'SAC' => [
                'A44:D47','A50:C52','A56:C58','A33:D38','A63:D68'
            ],
        ];
        $sheetId = SheetHelper::getIdFromUrl($url);
        // overview ranges contains a range to the SAC sheet therefore it is necessary to map the name of the SAC sheet and add it in SNS
        $sacName = null;
        
        if(!is_null($sheetId)){
            $client = SlideHelper::getClient();
            $data   = SheetHelper::getSheetsName($client,$sheetId);
            foreach($data as $value){
                $value_explode = explode(" ",$value);
                if(isset($value_explode[0]) && in_array($value_explode[0],array_keys($targets))){
                    $key = $value_explode[0];
                    $sheetNames[$key] = [];
                    $sheetNames['select'][$value] = $value;
                    foreach($targets[$key] as $target){
                        $range = "{$value}!{$target}";
                        $sheetNames[$key][$range] = $range;
                    }
                }
                if(is_null($sacName) && isset($value_explode[0])){
                    if($value_explode[0] == 'SAC'){
                        $sacName = $value;
                    }
                }
            }

            if(!is_null($sacName)){
                $sheetNames['SNS']["{$sacName}!A44:D47"] = "{$sacName}!A44:D47";
            }
        }
        
        return $sheetNames;
    }
}