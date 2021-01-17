<?php

namespace app\commands;

use yii;
use yii\console\Controller;
use yii\console\ExitCode;

use yii\helpers\Console;
use yii\helpers\ArrayHelper;

use app\modules\report\helpers\PresentationHelper;
use app\modules\report\helpers\SlideHelper;
use app\modules\report\helpers\SheetHelper;

use app\modules\report\models\Presentation;
use app\modules\report\models\Section;
use app\modules\report\models\SectionType;


/**
 *
 * This command create presentation on google  Slide
 *
 *
 * @author Eduardo Morales <xavierm@outlook.com>
 * @since 2.0
 */
class PresentationController extends Controller{
    
    public $presentationId;
    public $slidesIdsTemplate = [];
    public $slidesIds = [];
    public $pageElements = [];
    public $presentationTemplateId ='1Vqu9FGku2fD6U1cQJAgKlaQwo5IXgEFUa6dLliPU4-w';
    public $spreadsheetId;

    /**
     * funtion (DEFAULT) load model by id and runs presentation function to create Google Slide
     * @param int $id of model
     * @return int Exit code
     */
    public function actionIndex($id)
    {
        // get models
        $model = $this->findModel($id);
        if($model){
            Console::stdout("Runing Presentation model id: {$model->id} \n", Console::BOLD);
            $this->main($model);
        }
        return ExitCode::OK;
    }

    /**
     * main function that builds requests for google slide API responses
     * @param Presentation the loaded model
     * @return int Exit code
     */
    public function main($model){
        
        // Get the API client and construct the service object.
        $client = SlideHelper::getClient($model->userId);
        // map template presentation
        $this->slidesIdsTemplate = SlideHelper::getSlidesIDFromPresentationId($client,$this->presentationTemplateId);
        // convert model to array
        $presentationModel = ArrayHelper::toArray($model, [
            'app\modules\report\models\Presentation' => [
                'id',
                'userId',
                'name',
                'head_title',
                'title',
                'url_sheet',
                // the key name in array result => property name
                'date',
                // the key name in array result => anonymous function
                'static' => function ($model) {
                    $typeSection = SectionType::find()->where(['name' => 'static'])->one();
                    return $model->getSections()->where(['typeSection' => $typeSection->id])->with(['pages'])->asArray()->all();
                },
                'overview' => function($model){
                    $typeSection = SectionType::find()->where(['name' => 'fixed'])->one();
                    return $model->getSections()->where(['typeSection' => $typeSection->id])
                    ->with(['pages' => function($query){
                        $query->andWhere(['status' => 1])->with('pageElements');
                    }])->asArray()->all();
                },
                'categories' => function($model){
                    $typeSection = SectionType::find()->where(['name' => 'dinamic'])->one();
                    return $model->getSections()->where(['typeSection' => $typeSection->id])
                    ->with(['pages' => function($query){
                        $query->andWhere(['status' => 1])->with('pageElements');
                    }])->asArray()->all();
                },
                'sac' => function($model){
                    $typeSection = SectionType::find()->where(['name' => 'sac'])->one();
                    return $model->getSections()->where(['typeSection' => $typeSection->id])
                    ->with(['pages' => function($query){
                        $query->andWhere(['status' => 1])->with('pageElements');
                    }])->asArray()->all();
                },
               
                
            ],
        ]);
        // create copy    
        $this->presentationId = SlideHelper::getOrSetPresentationId($client,$this->presentationTemplateId,$model->name);
        // set spreadsheetId
        $this->spreadsheetId = SheetHelper::getIdFromUrl($model->url_sheet);

        try {
            // create cover    
            $requestCover = $this->createCover($presentationModel);
            // request cover
            SlideHelper::executeRequest($client,$requestCover,$this->presentationId); 
            // create section with sub sections
            $requestSection = $this->createSection($client,$presentationModel);
            // request section
            SlideHelper::executeRequest($client,$requestSection,$this->presentationId); 
            // create overview
            $requestOverView = $this->createOverView($client,$presentationModel);
            // request overview
            SlideHelper::executeRequest($client,$requestOverView,$this->presentationId); 
            // create Categories
            $requestCategories = $this->createCategories($client,$presentationModel);
            // request Categories
            SlideHelper::executeRequest($client,$requestCategories,$this->presentationId); 
            // create Sac
            $requestSac = $this->createSac($client,$presentationModel);
            // request Sac
            SlideHelper::executeRequest($client,$requestSac,$this->presentationId); 
            // delete slide template copy
            $this->deleteSlideTemplate($client);
            // update order slide   
            $this->updateOrder($client);
            // compose hiperlink
            $hiperlink = "https://docs.google.com/presentation/d/{$this->presentationId}/edit";
            // save on model link;
            $model->url_presentation = $hiperlink;
            // set name to presentation
            $presentation = SlideHelper::getPresentation($client,$this->presentationId);
            $model->name =  ($presentation) ? $presentation->title : $model->name;
            // change status presentation
            $this->changeStatus($model,0);
            // save model 
            $model->save();
            // show link
            Console::stdout("Open Link: \n https://docs.google.com/presentation/d/{$this->presentationId}/edit \n", Console::BOLD);
        } catch (\Google\Service\Exception $th) {
            // change status presentation
            $this->changeStatus($model,0);
            print_r($th->getMessage());
            // delete file
            echo "delete file by error..";
            SlideHelper::deleteFile($client,$this->presentationId);
        }
        
        
        
        return ExitCode::OK;
    }

    /** 
     * create request array to cover slide
     * @param Array the presentation model
     * @return Array request of cover
     */
    protected function createCover($presentationModel){
        // get cover page id
        $objectId = $this->slidesIdsTemplate[1];
        // head_title
        $replaceText = $presentationModel['head_title'];
        $containsText = "{{HEAD_TITLE}}";
        $pageObjectIds = [$objectId];
        $request[] = SlideHelper::replaceAllText($replaceText,$pageObjectIds,$containsText);
        // title
        $replaceText = $presentationModel['title'];
        $containsText = "{{TITLE}}";
        $pageObjectIds = [$objectId];
        $request[] = SlideHelper::replaceAllText($replaceText,$pageObjectIds,$containsText);
        // date
        $replaceText = Yii::$app->formatter->asDate($presentationModel['date'], 'dd/MM/yyyy');
        $containsText = "{{DATE}}";
        $pageObjectIds = [$objectId];
        $request[] = SlideHelper::replaceAllText($replaceText,$pageObjectIds,$containsText);
        // set slideId for update position
        $this->slidesIds[] = $objectId;

        return $request; 

    }

    /** 
     * create request array to slide with parent and child
     * @param Array the presentation model
     * @return Array request of section
     */
    protected function createSection($client,$presentationModel){
        // set slides
        $slidesModel = $presentationModel['static'];
        $request = [];
        $index = 1;
        
        for ($p=0; $p < sizeOf($slidesModel); $p++) { 
            
            $replaceText = $slidesModel[$p]['head_title'];
            $objectId = (strlen($replaceText) >= 24) ? $this->slidesIdsTemplate[3]: $this->slidesIdsTemplate[2];
            $newobjId = "slide_{$index}";
            
            $request[] = SlideHelper::duplicateObject($objectId,$newobjId);
            $containsText = "{{HEAD_TITLE}}";
            $pageObjectIds = [$newobjId];
            $request[] = SlideHelper::replaceAllText($replaceText,$pageObjectIds,$containsText);
            // set slideId for update position
            $this->slidesIds[] = $newobjId;
            
            if(count($slidesModel[$p]['pages'])){
                for($s=0; $s < sizeOf($slidesModel[$p]['pages']) ; $s++){
                    $index++;
                    $objectId = $this->slidesIdsTemplate[rand(4,6)];
                    $newobjId = "slide_{$index}";
                    $request[] = SlideHelper::duplicateObject($objectId,$newobjId);
                    $replaceText = $slidesModel[$p]['pages'][$s]['title'];
                    $containsText = "{{TITLE}}";
                    $pageObjectIds = [$newobjId];
                    $request[] = SlideHelper::replaceAllText($replaceText,$pageObjectIds,$containsText);
                    // set slideId for update position
                    $this->slidesIds[] = $newobjId;
                }
            }
            
            $index++;
            
        }
        
        return $request;
    }

    /** 
     * create request array to slide with static section AKA overview
     * @param Array the presentation model
     * @return Array request of Overview section
     */
    protected function createOverView($client,$presentationModel){
        // set slides
        $slidesModel = $presentationModel['overview'];
        $request = [];
        
        // continue index
        $numberSlide = SlideHelper::getNumberSlideId(end($this->slidesIds));
        $index = $numberSlide + 1;

        for ($p=0; $p < sizeOf($slidesModel); $p++) { 
            
            $replaceText = $slidesModel[$p]['head_title'];
            $objectId = (strlen($replaceText) >= 24) ? $this->slidesIdsTemplate[3]: $this->slidesIdsTemplate[2];
            $newobjId = "slide_{$index}";
            
            $request[] = SlideHelper::duplicateObject($objectId,$newobjId);
            $containsText = "{{HEAD_TITLE}}";
            $pageObjectIds = [$newobjId];
            $request[] = SlideHelper::replaceAllText($replaceText,$pageObjectIds,$containsText);
            // set slideId for update position
            $this->slidesIds[] = $newobjId;
            
            if(count($slidesModel[$p]['pages'])){
               
                for($s=0; $s < sizeOf($slidesModel[$p]['pages']) ; $s++){
                    $index++;
                    $replaceText = $slidesModel[$p]['pages'][$s]['title'];
                    $objectId = $this->slidesIdsTemplate[7];

                    $containsText = "{{TITLE}}";
                    $pageObjectIds = [$objectId];
                    $request[] = SlideHelper::replaceAllText($replaceText,$pageObjectIds,$containsText);

                    // loop over page elements
                    if(count($slidesModel[$p]['pages'][$s]['pageElements'])){
                       
                        $elementsTemplate = SlideHelper::getPageElementFromSlideId($client,$this->presentationTemplateId,$objectId);
                       
                        for ($e=0; $e < sizeOf($slidesModel[$p]['pages'][$s]['pageElements']); $e++) { 
                            $name = $slidesModel[$p]['pages'][$s]['pageElements'][$e]['name'];
                            
                            if(strpos($name, 'alt_table') === 0){
                                if(isset($elementsTemplate[$name])){
                                    $tableId = $elementsTemplate[$name]['id'];
                                    $range = $slidesModel[$p]['pages'][$s]['pageElements'][$e]['value'];
                                    $recordsObjects = SheetHelper::getValuesFromRange($client,$this->spreadsheetId,$range);
                                    
                                    if(is_array($recordsObjects)){
                                        $countRowsToCreate = count($recordsObjects) - 2; // less header
                                        if($countRowsToCreate > 1){
                                            $request[] = SlideHelper::insertTableRows($tableId,$rowIndex = 1,$insertBelow = true,$countRowsToCreate);
                                        }
                                        
                                        foreach($recordsObjects as $rowIndex => $recordsObject){
                                            foreach($recordsObject as $columnIndex => $value){
                                                $value = SheetHelper::sanitazeStringValuesNullAndUpperFirtsLetter($value);
                                                //$request[] = SlideHelper::deleteTextCellTable($tableId,$rowIndex,$columnIndex);
                                                $request[] = SlideHelper::insertTextCellTable($tableId,$rowIndex,$columnIndex,$value);
                                                
                                            }
                                        }
                                    }
                                }
                                
                            }
                        }

                    }

                }
            }
            
            $index++;
            
        }
        $this->slidesIds[] = $this->slidesIdsTemplate[7];
        
        return $request;
    }

    /** 
     * create request array to slide with static section AKA overview
     * @param Array the presentation model
     * @return Array request of categories
     */
    protected function createCategories($client,$presentationModel){
        // set slides
        $slidesModel = $presentationModel['categories'];
        $request = [];
        // continue index
        $penultimate = $this->slidesIds[ count($this->slidesIds ) - 2  ];
        $numberSlide = SlideHelper::getNumberSlideId($penultimate);
        $index = $numberSlide + 1;

        $numberSlides = [9,10,11,12,13,14];
        // map chart
        $charts = SheetHelper::getChartsBySheetID($client,$this->spreadsheetId);
        
        for ($p=0; $p < sizeOf($slidesModel); $p++) { 
            
            $replaceText = $slidesModel[$p]['head_title'];
            $objectId = (strlen($replaceText) >= 24) ? $this->slidesIdsTemplate[3]: $this->slidesIdsTemplate[8];
            $newobjId = "slide_{$index}";
            
            $request[] = SlideHelper::duplicateObject($objectId,$newobjId);
            $containsText = "{{HEAD_TITLE}}";
            $pageObjectIds = [$newobjId];
            $request[] = SlideHelper::replaceAllText($replaceText,$pageObjectIds,$containsText);
            // set slideId for update position
            $this->slidesIds[] = $newobjId;

            if(count($slidesModel[$p]['pages'])){
                for($s=0; $s < sizeOf($slidesModel[$p]['pages']) ; $s++){
                    $index++;
                    $page = $slidesModel[$p]['pages'][$s];
                    $replaceTextMain = $page['title'];
                    $objectId = $this->slidesIdsTemplate[$numberSlides[$s]];
                    
                    $newobjId = "slide_{$index}";

                    

                    // map template page
                    $elementsTemplate = SlideHelper::getPageElementFromSlideId($client,$this->presentationTemplateId,$objectId);
                    $uuid = SlideHelper::randonString();
                    switch ($numberSlides[$s]) {
                        case 10:
                            if(isset($elementsTemplate['alt_table_facebook']) && isset($elementsTemplate['alt_table_instagram'])){
                                $alt_table_facebook_id = $elementsTemplate['alt_table_facebook']['id'];
                                $alt_table_instagram_id = $elementsTemplate['alt_table_instagram']['id'];
                                
                                $objectIds = [
                                    "{$objectId}" => $newobjId,
                                    "{$alt_table_facebook_id}" => "alt_table_facebook_{$uuid}",
                                    "{$alt_table_instagram_id}" => "alt_table_instagram_{$uuid}",
                                ];
                                $request[] = SlideHelper::duplicateObjectTable($objectId,$objectIds);
                            }
                            break;
                        case 11:
                            if(isset($elementsTemplate['alt_table_best_publication_facebook']) && isset($elementsTemplate['alt_table_best_publication_instagram'])){
                                $alt_table_facebook_id = $elementsTemplate['alt_table_best_publication_facebook']['id'];
                                $alt_table_instagram_id = $elementsTemplate['alt_table_best_publication_instagram']['id'];
                                $alt_link_facebook_id = $elementsTemplate['alt_link_facebook']['id'];
                                $alt_link_instagram_id = $elementsTemplate['alt_link_instagram']['id'];
                                $objectIds = [
                                    "{$objectId}" => $newobjId,
                                    "{$alt_table_facebook_id}" => "alt_table_best_publication_facebook_{$uuid}",
                                    "{$alt_table_instagram_id}" => "alt_table_best_publication_instagram_{$uuid}",
                                    "{$alt_link_facebook_id}" => "alt_link_facebook_{$uuid}",
                                    "{$alt_link_instagram_id}" => "alt_link_instagram_{$uuid}",
                                ];
                                $request[] = SlideHelper::duplicateObjectTable($objectId,$objectIds);
                            }
                            break;
                        default:
                            $request[] = SlideHelper::duplicateObject($objectId,$newobjId);
                            break;
                    }
                    
                    $containsMainText = "{{TITLE}}";
                    $pageObjectIds = [$newobjId];
                    $request[] = SlideHelper::replaceAllText($replaceTextMain,$pageObjectIds,$containsMainText);
                    
                    // loop page elements
                    if(count($slidesModel[$p]['pages'][$s]['pageElements'])){
                        $pageElements = $slidesModel[$p]['pages'][$s]['pageElements'];
                        for ($e=0; $e < sizeOf($pageElements); $e++) { 
                            $name = $pageElements[$e]['name'];
                            $value = $pageElements[$e]['value'];
                            $brandName = $this->getBrandForRange($value);
                
                            // if graph
                            if(strpos($name, 'alt_graph') === 0){
                                $sheetId  = SheetHelper::getSheetIdByName($client,$this->spreadsheetId,$value);
                                if(isset($charts[$sheetId])){
                                    //total of chart
                                    $series = $charts[$sheetId][$e]['series'];
                                    $range = SheetHelper::setRangeBySourcesSeries($value,$series);
                                    $values = SheetHelper::getValuesFromRange($client,$this->spreadsheetId,$range);
                                    
                                    $total = SheetHelper::addValuesOfTheGraphs($values);
                                    $containsText = "{{total_$e}}";
                                    $request[] = SlideHelper::replaceAllText($total,$pageObjectIds,$containsText);
                                    // print chart
                                    $request[] = SlideHelper::replaceAllShapesWithSheetsChart("{{$name}}",$this->spreadsheetId,$charts[$sheetId][$e]['chartId'],$pageObjectIds);
                                    // print month
                                    $month = (isset($values[0][1])) ? ucfirst(strtolower($values[0][1])) : "UNDEFINED";
                                    $containsText = "{{month}}";
                                    $request[] = SlideHelper::replaceAllText($month,$pageObjectIds,$containsText);
                                }
                            }
                            // if table
                            if(strpos($name, 'alt_table') === 0){
                                if(isset($elementsTemplate[$name])){
                                    $tableId = "{$name}_{$uuid}";
                                    $range = $value;
                                    $recordsObjects = SheetHelper::getValuesFromRange($client,$this->spreadsheetId,$range);
                                    $countRowsToCreate = count($recordsObjects) - 1; // less header
                                    $countColumnsToCreate = ($countRowsToCreate) ? count($recordsObjects[0]) : 5;
                                    if($countRowsToCreate > 1){
                                        $request[] = SlideHelper::insertTableRows($tableId,$rowIndex = 0,$insertBelow = true,$countRowsToCreate);
                                    }
                                    if($countColumnsToCreate > 7){
                                        $number = $countColumnsToCreate - 7;
                                        $request[] = SlideHelper::insertTableColumns($tableId,$rowIndex = 1,$columnIndex = 1,$insertRight = true,$number);
                                    }
                                    $request_sentences = $this->addDataMainParagraphOfCategories($recordsObjects,$pageObjectIds,$brandName);

                                    foreach($request_sentences as $sentence){
                                        $request[] = $sentence;
                                    }
                                    
                                    foreach($recordsObjects as $rowIndex => $recordsObject){
                                        // when it is empty we fill the row with red
                                        if(empty($recordsObject)){
                                            $rgbColor = array(
                                                "red" => "0.64705884",
                                                "blue" => "0.20392157"
                                            );
                                            $request[] = SlideHelper::updateTableCellPropertiesColorBackground($tableId,$rowIndex,$columnIndex = 0,$rowSpan = 1,$countColumnsToCreate,$rgbColor);;
                                        }

                                        
                                        foreach($recordsObject as $columnIndex => $value){
                                            $flag_bold = (sizeOf($recordsObjects) -1 == $rowIndex) ? $rowIndex : null;
                                            $value = SheetHelper::sanitazeStringValuesNullAndUpperFirtsLetter($value);
                                            $request[] = SlideHelper::insertTextCellTable($tableId,$rowIndex,$columnIndex,$value);
                                            // adding color white to header
                                            if($rowIndex == 0 && !empty($value)){ 
                                                $request[] = SlideHelper::setHeaderColor($tableId,$rowIndex,$columnIndex);
                                            }
                                            // adding bold
                                            if(!is_null($flag_bold)){
                                                $rgbColor = array(
                                                    "red" => "0.9372549",
                                                    "green" => "0.9372549",
                                                    "blue" => "0.9372549"
                                                );
                                                $request[] = SlideHelper::updateTableCellPropertiesColorBackground($tableId,$rowIndex,$columnIndex,$rowSpan = 1,$columnSpan = 1,$rgbColor);
                                                $request[] = SlideHelper::updateBoldStyleText($tableId,$flag_bold,$columnIndex, $bold = true);
                                            }
                                            if($rowIndex == 3){
                                                $rgbColor = array(
                                                    "red" => "0.9372549",
                                                    "green" => "0.9372549",
                                                    "blue" => "0.9372549"
                                                );
                                                $request[] = SlideHelper::updateTableCellPropertiesColorBackground($tableId,$rowIndex,$columnIndex,$rowSpan = 1,$columnSpan = 1,$rgbColor);
                                                $request[] = SlideHelper::updateBoldStyleText($tableId,$rowIndex,$columnIndex, $bold = true);
                                            }
                                        }
                                        
                                    }
                                    
                                    $request[] = SlideHelper::setHeaderColorBackground($tableId,$countColumnsToCreate);
                                    $dimension = [
                                        "magnitude"=> 396200,
                                        "unit"=> "EMU"
                                    ];
                                    $request[] = SlideHelper::updateTableRowProperties($tableId,$dimension);
                                    
                                }
                            }

                        }
                    }

                    // best publication
                    $best_publication_title = \app\modules\report\models\DinamicForm::TITLE_FIXED['best_publication'];
                    if(strpos($replaceTextMain, $best_publication_title) === 0){
                        $sheetNames = SheetHelper::getSheetsName($client,$this->spreadsheetId);
                        $request[] = SlideHelper::replaceAllText($brandName,$pageObjectIds,"{{brand}}");
                        if(isset($sheetNames[0])){
                            $sheetName = $sheetNames[0];
                            $recordsObjects = SheetHelper::getValuesFromRange($client,$this->spreadsheetId,$sheetName);
                            $records = SheetHelper::getRecordOrderBy($recordsObjects,['Engagement Rate in %',SORT_DESC]);
                            
                            $records_publications = SheetHelper::getRecordsWhere($records,['Tags'=> $brandName]);
                            
                            
                            if($brandName == 'HA' && empty($records_publications)){
                                $brandName = ($brandName == 'HA') ? 'H&A' : $brandName;
                                $records_publications = SheetHelper::getRecordsWhere($records,['Tags'=> $brandName]);
                            }
                            
                            // full table best publication facebook
                            if(isset($records_publications['facebook'][0])){
                                $record = $records_publications['facebook'][0];
                                
                                // firts version spreedsheets firts sheet 
                                $targets_old = ['Topic','Facebook Post Stream Reactions','Facebook Post Stream Comments (SUM)','Facebook Post Stream Shares (SUM)','Total Engagements (SUM)','Post Reach (SUM)','Engagement Rate in %'];
                                // new version spreedsheets firts sheet 
                                $target_news = ['Campaign Name','Reacciones / Likes','Comentarios','Compartidos','Interacciones','Post Reach (SUM)','Engagement Rate in %'];
                                // look each one of target depending of records
                                $is_flag_target_new = (in_array('Comentarios',$record)) ? true : false;
                                $targets = ($is_flag_target_new) ? $target_news : $targets_old;

                                $tableId = "alt_table_best_publication_facebook_{$uuid}";;
                                foreach ($targets as $targetIndex => $target) {
                                    if(isset($record[$target])){
                                        $value = $record[$target];
                                        if(is_numeric($value)){
                                            $value = number_format($value, 0,',','.');
                                        }
                                        if($target == "Engagement Rate in %"){
                                            $value = str_replace(',', '.', $value);
                                            $value =  (string) SlideHelper::roundout ($value, 2);
                                            $value = "{$value}%";
                                        }

                                        if($target == 'Topic' && $value == ''){
                                            $value = substr($record['Outbound Post'],0,14);
                                            $value .= " ...";
                                        }
                                        $request[] = SlideHelper::insertTextCellTable($tableId,1,$targetIndex,$value);
                                    }
                                }
                                // update link
                                $value = ($record['Topic'] == '') ? substr($record['Outbound Post'],0,14): $record['Topic']; 
                                $request[] = SlideHelper::replaceAllText($value,$pageObjectIds,"{{link_face}}");
                                if(isset($elementsTemplate['alt_link_facebook'])){
                                    $objectId = "alt_link_facebook_{$uuid}";
                                    $request[] = SlideHelper::updateLinkText($objectId,$record['Permalink']);
                                }
                            }
                            // full table best publication instagram
                            if(isset($records_publications['instagra'][0])){
                                $record = $records_publications['instagra'][0];
                                $targets = ['Topic','Facebook Post Stream Reactions','Facebook Post Stream Comments (SUM)','Facebook Post Stream Shares (SUM)','Total Engagements (SUM)','Post Reach (SUM)','Engagement Rate in %'];
                                $tableId = "alt_table_best_publication_instagram_{$uuid}";
                                foreach ($targets as $targetIndex => $target) {
                                    $value = $record[$target];
                                    if(is_numeric($value)){
                                        $value = number_format($value, 0,',','.');
                                    }
                                    if($target == "Engagement Rate in %"){
                                        $value = str_replace(',', '.', $value);
                                        $value =  (string) SlideHelper::roundout ($value, 2);
                                        $value = "{$value}%";
                                    }
                                    if($target == 'Topic' && $value == ''){
                                        $value = substr($record['Outbound Post'],0,14);
                                    }
                                    $request[] = SlideHelper::insertTextCellTable($tableId,1,$targetIndex,$value);
                                }
                                // update link
                                $value = ($record['Topic'] == '') ? substr($record['Outbound Post'],0,14): $record['Topic']; 
                                $request[] = SlideHelper::replaceAllText($value,$pageObjectIds,"{{link_insta}}");
                                if(isset($elementsTemplate['alt_link_instagram'])){
                                    $objectId = "alt_link_instagram_{$uuid}";
                                    $request[] = SlideHelper::updateLinkText($objectId,$record['Permalink']);
                                }
                            }
                        }
                        
                    }
                    // set slideId for update position
                    $this->slidesIds[] = $newobjId;

                    
                }
            }    
            $index++;
        }
        return $request;
        
    }

    /** 
     * create get brand name for the range of spreedsheet
     * @param Array the range of spreedsheet
     * @return String brand
     */
    protected function getBrandForRange($range){
        $range_explode = explode(" ",$range);
        $brand = null;
        if(isset($range_explode[1])){
            $brand = $range_explode[1];
        }
        return $brand;
    }

     /** 
     * add data to the main paragraph
     * @param Array $recordsObjects records sheet
     * @param Array $pageObjectIds obejct id elements
     * @param String $brandName name brand: HA,HE ..
     * @return Array request of paragraph
     */
    protected function addDataMainParagraphOfCategories($recordsObjects = [],$pageObjectIds,$brandName)
    {
       if(!empty($recordsObjects) && isset($recordsObjects[1])){
         
        $brand = $brandName;
        $first_period = ucfirst($recordsObjects[1][0]);
        $first_period_explode = explode(" ",$first_period);

        $penultimate_period = $recordsObjects[count($recordsObjects) -3][0];
        $penultimate_explode = explode(" ",$penultimate_period);

        $antepenultimate = $recordsObjects[count($recordsObjects) -2][0];
        $antepenultimate_explode = explode(" ",$antepenultimate);

        $month = $first_period_explode[0];
        $year_from = $first_period_explode[1];

        $month_from = $antepenultimate_explode[0];
        $month_to = $penultimate_explode[0];

        $last_year_period = explode(" ",$recordsObjects[2][0]);
        $year_to = end($last_year_period);

        $containsText = "{{brand}}";
        $request[] = SlideHelper::replaceAllText($brand,$pageObjectIds,$containsText);
        $containsText = "{{month}}";
        $request[] = SlideHelper::replaceAllText($month,$pageObjectIds,$containsText);
        $containsText = "{{year_from}}";
        $request[] = SlideHelper::replaceAllText($year_from,$pageObjectIds,$containsText);
        $containsText = "{{month_from}}";
        $request[] = SlideHelper::replaceAllText($month_from,$pageObjectIds,$containsText);
        $containsText = "{{month_to}}";
        $request[] = SlideHelper::replaceAllText($month_to,$pageObjectIds,$containsText);
        $containsText = "{{year_to}}";
        $request[] = SlideHelper::replaceAllText($year_to,$pageObjectIds,$containsText);
        return $request;
       }
    }

    /** 
     * create request array to slide Sac
     * @param Client Google client
     * @param Array the presentation model
     * @return Array request of cover
     */
    protected function createSac($client,$presentationModel){
        // set slides
        $slidesModel = $presentationModel['sac'];
        $request = [];
        
        // continue index
        $numberSlide = SlideHelper::getNumberSlideId(end($this->slidesIds));
        $index = $numberSlide + 1;
        // to update properties styles
        $tablesPropertiesToUpdate = [];
        // map chart
        $charts = SheetHelper::getChartsBySheetID($client,$this->spreadsheetId);

        $numberSlides = [16,17,18,19,20,21];

        for ($p =0; $p  < sizeOf($slidesModel) ; $p ++) { 
            $replaceText = $slidesModel[$p]['head_title'];
            $objectId = (strlen($replaceText) >= 24) ? $this->slidesIdsTemplate[3]: $this->slidesIdsTemplate[15];
            $containsText = "{{HEAD_TITLE}}";
            $request[] = SlideHelper::replaceAllText($replaceText,[$objectId],$containsText);

            // set slideId for update position
            $this->slidesIds[] = $objectId;

            if(isset($slidesModel[$p]['pages'])){
                if(count($slidesModel[$p]['pages'])){
                    for($s=0; $s < sizeOf($slidesModel[$p]['pages']) ; $s++){
                        $page = $slidesModel[$p]['pages'][$s];
                        $replaceTextMain = $page['title'];
                        $objectId = $this->slidesIdsTemplate[$numberSlides[$s]];

                        $containsMainText = "{{TITLE}}";
                        $request[] = SlideHelper::replaceAllText($replaceTextMain,[$objectId],$containsMainText);
                        // map template page
                        $elementsTemplate = SlideHelper::getPageElementFromSlideId($client,$this->presentationTemplateId,$objectId);
                        // loop page elements
                        if(isset($slidesModel[$p]['pages'][$s]['pageElements'])){
                            if(count($slidesModel[$p]['pages'][$s]['pageElements'])){
                                $pageElements = $slidesModel[$p]['pages'][$s]['pageElements'];

                                for ($e=0; $e < sizeOf($pageElements); $e++) { 
                                    $name = $pageElements[$e]['name'];
                                    $value = $pageElements[$e]['value'];
                                    // if table
                                    if(strpos($name, 'alt_table') === 0){
                                        if(isset($elementsTemplate[$name])){
                                            $tableId = $elementsTemplate[$name]['id'];
                                            $range = $value;
                                            $recordsObjects = SheetHelper::getValuesFromRange($client,$this->spreadsheetId,$range);
                                          
                                            if(!empty($recordsObjects)){
                                                // adding properties to change table latter
                                                //$tablesPropertiesToUpdate[$objectId][] = $elementsTemplate;
                                                $countRowsToCreate = count($recordsObjects) - 1; // less header
                                                $countColumnsToCreate = ($countRowsToCreate) ? count($recordsObjects[0]) : 4;
                                                
                                                if($countRowsToCreate > 1){
                                                    $rowIndex = ($name == "alt_table_type_messages_sac") ? 2: 0;
                                                    $countRowsToCreate = ($name == "alt_table_type_messages_sac") ? $countRowsToCreate - 2: $countRowsToCreate;
                                                    $request[] = SlideHelper::insertTableRows($tableId,$rowIndex,$insertBelow = true,$countRowsToCreate);
                                                }

                                                if($countColumnsToCreate > $elementsTemplate[$name]['columns']){
                                                    $number = $countColumnsToCreate - $elementsTemplate[$name]['columns'];
                                                    $request[] = SlideHelper::insertTableColumns($tableId,$rowIndex = 1,$columnIndex = 1,$insertRight = true,$number);
                                                }
                                                
                                                foreach($recordsObjects as $rowIndex => $recordsObject){
                                                    
                                                    foreach($recordsObject as $columnIndex => $value){
                                                        $value = SheetHelper::sanitazeStringValuesNullAndUpperFirtsLetter($value);
                                                        
                                                        $request[] = SlideHelper::insertTextCellTable($tableId,$rowIndex,$columnIndex,$value);
                                                        // adding color white to header
                                                        if($rowIndex == 0 && !empty($value)){ 
                                                            $request[] = SlideHelper::setHeaderColor($tableId,$rowIndex,$columnIndex);
                                                        }
                                                    }
                                                    
                                                }
                                                
                                                $request[] = SlideHelper::setHeaderColorBackground($tableId,$countColumnsToCreate);
                                                $dimension = [
                                                    "magnitude"=> 396200,
                                                    "unit"=> "EMU"
                                                ];
                                                $request[] = SlideHelper::updateTableRowProperties($tableId,$dimension);

                                                $elementsTemplate['rows'] = count($recordsObjects);
                                                $elementsTemplate['columns'] = count($recordsObjects[0]);
                                                $tablesPropertiesToUpdate[$objectId][] = $elementsTemplate;

                                            }
                                            

                                        }
                                    }
                                    
                                    // if graph
                                    if(strpos($name, 'alt_graph') === 0){
                                        $sheetId  = SheetHelper::getSheetIdByName($client,$this->spreadsheetId,$value);
                                        
                                        if(isset($charts[$sheetId])){
                                            //total of chart
                                            $indexGraph = ($numberSlides[$s] == 20) ? $e + 2 : $e;
                                            // print chart
                                            $request[] = SlideHelper::replaceAllShapesWithSheetsChart("{{$name}}",$this->spreadsheetId,$charts[$sheetId][$indexGraph]['chartId'],$objectId);
                                        }
                                    }
                                }
                                if(isset($recordsObjects[0])){
                                    $firts_period = $recordsObjects[0][1];
                                    $second_period = $recordsObjects[0][2];
                                    $firts_period_explode = explode(" ",$firts_period);
                                    $second_period_explode = explode(" ",$second_period);
                                    $containsMainText = "{{month_from}}";
                                    $month_from = $firts_period_explode[0]; 
                                    $request[] = SlideHelper::replaceAllText(ucfirst($month_from),[$objectId],$containsMainText);
                                    $containsMainText = "{{month_to}}";
                                    $month_to = $second_period_explode[0]; 
                                    $request[] = SlideHelper::replaceAllText(ucfirst($month_to),[$objectId],$containsMainText);
                                }

                            }
                        }

                        
                        // set slideId for update position
                        $this->slidesIds[] = $objectId;
                    }
                }
            }
        }
        $propetiesTables = $this->UpdateTablePropertiesSac($tablesPropertiesToUpdate);
        foreach($propetiesTables as $propetiesTable){
            $request[] = $propetiesTable;
        }
        
        return $request;
    }
    /** 
     * create request array to properties slide Sac
     * @param Array Properties to update Sac
     * @return Array request of cover
     */
    protected function UpdateTablePropertiesSac($tablesPropertiesToUpdate){
        $request = [];
        // update table alt_table_received_sac
        $slideId = $this->slidesIdsTemplate[18];
        $table_received_sac = $tablesPropertiesToUpdate[$slideId]; 
        $tableId = $tablesPropertiesToUpdate[$slideId][0]['alt_table_received_sac']['id'];
        $columns = (int) $tablesPropertiesToUpdate[$slideId][0]['columns'];
        $rows = (int) $tablesPropertiesToUpdate[$slideId][0]['rows'];
        // adding color red backgorund alt_table_received_sac
        $rgbColor = array(
            "red" => "0.64705884",
            "blue" => "0.20392157"
        );
        $request [] = SlideHelper::updateTableCellPropertiesColorBackground($tableId,$rowIndex = 1,$columnIndex = 0,$rowSpan = 1,$columns,$rgbColor);
        // add white to the second row
        // $columns -1  because the last column in the second row has no text whatsoever
        for ($i=0; $i < $columns -1 ; $i++) { 
            $request[] = SlideHelper::setHeaderColor($tableId,1,$i);
        }
        // delete bold
        for ($r=2; $r < $rows ; $r++) { 
            for ($c=0; $c < $columns ; $c++) { 
                $request[] = SlideHelper::updateBoldStyleText($tableId,$r,$c, $bold = false);
            }
        }
        
        
        return $request;
    }
    /** 
     * send request delete slides template
     * @param Google_Client $client
     * @return void
     */
    protected function deleteSlideTemplate($client){
        $slidesIdToDelete = [2,3,4,5,6,8,9,10,11,12,13,14];
        $requestD = [];
        // delete pages template
        for($s = 0; $s < sizeOf($slidesIdToDelete); $s++){
            $id = $slidesIdToDelete[$s];
            $requestD[] = SlideHelper::deleteObject($this->slidesIdsTemplate[$id]);
        }
        SlideHelper::executeRequest($client,$requestD,$this->presentationId);  
    }
    /** 
     * send request updating slides order
     * @param Google_Client $client
     * @return void
     */
    protected function updateOrder($client){

        $slideObjectIds = $this->slidesIds;
        for ($s=0; $s <sizeOf($slideObjectIds) ; $s++) { 
            $requestF[] = SlideHelper::updateSlidesPosition($slideObjectIds[$s],$s);
            SlideHelper::executeRequest($client,$requestF,$this->presentationId); 
        }
    }
    /** 
     * map and asing elements of template depred
     * @param Google_Client $client
     * @param string $presentationId
     * @param string $newName
     * @return void
     */
    protected function mapElementPresentation($client){
        // map element slides
        $service = new \Google_Service_Slides($client);
        $presentation = $service->presentations->get($this->presentationTemplateId);
        $slides = $presentation->getSlides();
        printf("The presentation contains %s slides:\n", count($slides));
        
        $target = ['alt_head_title','alt_title','alt_date','alt_image','alt_paragraph','alt_link'];
        $pageElements = [];
        
        foreach ($slides as $i => $slide) {
            $objId = $slide->getObjectId();
            $this->slidesIdsTemplate[$i + 1] = $objId;
            foreach($slide->getPageElements() as $e => $element){
                if(isset($element->title)){
                    if(strpos($element->title, 'alt_') === 0){
                        $this->pageElements[$objId][$element->title] = [
                            'id' =>  $element->getObjectId(),
                          ];
                    }
                }
            }
        }
    }

    /**
     * Finds the Presentation model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Presentation the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Presentation::findOne(['status' => 1,'id' => $id])) !== null) {
            return $model;
        }

        return false;
    }
    /**
     * Change status of presentation model.
     * @param  Presentation $model
     * @return void
     */
    protected function changeStatus($model,$status){
        // change status
        $model->status = $status;
        // save model 
        $model->save();
    }

}