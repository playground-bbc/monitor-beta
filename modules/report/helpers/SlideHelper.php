<?php
namespace app\modules\report\helpers;

use yii;
use yii\db\Expression;
use yii\helpers\Console;
use app\modules\report\models\Presentation;
require_once Yii::getAlias('@vendor') . '/autoload.php'; // call google client

/**
 *
 * @author Eduardo Morales <eduardo@montana-studio.com>
 * @group  Montana-Studio LG 
 */


class SlideHelper{

    public static function getClient(){
        $client = new \Google_Client();
        $client->setApplicationName('report-lg-montana-studio');
        $client->setScopes(Presentation::SCOPES);
        $pathCredentials = \Yii::getAlias('@app/credentials/credentials.json');
        $client->setAuthConfig($pathCredentials);
        $client->setAccessType('offline');
        $client->setPrompt('select_account consent');

        $tokenPath = \Yii::getAlias('@app/credentials/token.json');
        if (file_exists($tokenPath)) {
            $accessToken = json_decode(file_get_contents($tokenPath), true);
            $client->setAccessToken($accessToken);
        }

        // If there is no previous token or it's expired.
        if ($client->isAccessTokenExpired()) {
            // Refresh the token if possible, else fetch a new one.
            if ($client->getRefreshToken()) {
                $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
            } else {
                throw new Exception("Error with acces token");
            }    
            // Save the token to a file.
            if (!file_exists(dirname($tokenPath))) {
                mkdir(dirname($tokenPath), 0700, true);
            }
            file_put_contents($tokenPath, json_encode($client->getAccessToken()));
        }
        return $client;
    }

    public static function getPresentation($client,$presentationId){
        $service = new \Google_Service_Slides($client);
        $presentation = $service->presentations->get($presentationId);
        return (isset($presentation->title)) ? $presentation : false;
    }
    /**
     * Search google drive if there is a presentation with the same name, if there is, 
     * proceed to give it a version name, proceed to invoke the setCopyPresentation function 
     * in order to give the copy a new name and return its id
     * @param Google_Client $client
     * @param string $presentationTemplateId
     * @param string $filename
     * @return string presentationId id for the new presentation
     */
    public static function getOrSetPresentationId($client,$presentationTemplateId,$fileName){
        
        $driveService = new \Google_Service_Drive($client);
        $parameters['q'] = "mimeType='application/vnd.google-apps.presentation' and name contains '{$fileName}' and trashed=false";
        
        $parameters['orderBy'] = "createdTime desc";
        $driveFiles = $driveService->files->listFiles($parameters);
        
        if(isset($driveFiles->files) && count($driveFiles->files)){
            $presentationName = $driveFiles->files[0]->name;
            
            $fileName = self::setVersionName($presentationName);
            $presentationId = self::setCopyPresentation($client,$presentationTemplateId,$fileName);
        }else{
            print 'Create a copy from template presentation '."\n";
            $presentationId = self::setCopyPresentation($client,$presentationTemplateId,$fileName);
        }

        return $presentationId;
    }

    /** 
     * create copy presentation
     * @param Google_Client $client
     * @param string $presentationId
     * @param string $newName
     * @return string presentationId id for the new presentation
     */
    public static function setCopyPresentation($client,$presentationId,$newName){
        // copy original presentations
        $driveService = new \Google_Service_Drive($client);

        // Trying copy slide
        $copy = new \Google_Service_Drive_DriveFile(array(
            'name' => $newName
        ));
        $driveResponse = $driveService->files->copy($presentationId, $copy);
        $presentationCopyId = $driveResponse->id;
        Console::stdout("Copy Slide: \n https://docs.google.com/presentation/d/${presentationCopyId}/edit \n", Console::BOLD);

        return $presentationCopyId;
    }

    /** 
     * map and asing elements of template
     * @param Google_Client $client
     * @param string $presentationId
     * @param string $newName
     */
    public static function getSlidesIDFromPresentationId($client,$presentationId){
        // map element slides
        $service = new \Google_Service_Slides($client);
        $presentation = $service->presentations->get($presentationId);
        $slides = $presentation->getSlides();
        
        $slidesID = [];
        
        foreach ($slides as $i => $slide) {
            $objId = $slide->getObjectId();
            $slidesID[$i + 1] = $objId;
        }
        return $slidesID;
    }

    /** 
     * show elements from content
     */
    public static function getPageElementFromSlideId($client,$presentationId,$slideId){
        printf("Get elements Page from slideId:  %s \n", $slideId);
        // map element slides
        $service = new \Google_Service_Slides($client);
        $presentationPage = $service->presentations_pages->get($presentationId,$slideId);
        $pageElement = [];


        foreach ($presentationPage as $i => $page) {

            if(isset($page->title)){
                if(strpos($page->title, 'alt_') === 0){
                    $pageElement[$page->title] = [
                        'id' =>  $page->getObjectId(),
                    ];
                    if(isset($page->table)){
                        $pageElement[$page->title]['rows']  = $page->table->rows;
                        $pageElement[$page->title]['columns']  = $page->table->columns;
                    }
                }

            }
        }

        return $pageElement;
        

    }
    /** 
     * execute request to google slide
     * @param Google_Client $client
     * @param array $request
     * @param string $presentationId
     */
    public static function executeRequest($client,$request,$presentationId){
        
        $batchUpdateRequest = new \Google_Service_Slides_BatchUpdatePresentationRequest(array(
            'requests' => $request
        ));
        $slidesService = new \Google_Service_Slides($client);
        $response = $slidesService->presentations->batchUpdate($presentationId, $batchUpdateRequest);

        return $response;
    }
    /** 
     * delete presentation on google slide
     * @param Google_Client $client
     * @param string $presentationId
     */
    public static function deleteFile($client,$presentationId){
        printf("Delete Presentation Id {$presentationId}:\n");
        $driveService = new \Google_Service_Drive($client);
        $driveService->files->delete($presentationId);
    }
    /** 
     * return request for delete slide
     * @param string $objId
     * @return Google_Service_Slides_Request
     */
    public static function deleteObject($objId){
        //  insert new text.
        printf("delete : %s", $objId);
        echo "\n";

        return new \Google_Service_Slides_Request(array(
            'deleteObject' => array(
                'objectId' => $objId,
            )
        ));
    }
    /** 
     * return request for duplicate slide
     * @param string $objId
     * @param array $newobjId
     * @return Google_Service_Slides_Request
     */
    public static  function duplicateObject($objId,$newobjId){
        //  insert new text.
        printf("doubling : %s - to {$newobjId}", $objId);
        echo "\n";
        return new \Google_Service_Slides_Request(array(
            'duplicateObject' => array(
                'objectId' => $objId,
                'objectIds' => [
                    "{$objId}" => $newobjId,
                ],
            )
        ));
    }

     /** 
     * return request for duplicate slide
     * @param string $objId
     * @param array $newobjId
     * @return Google_Service_Slides_Request
     */
    public static  function duplicateObjectTable($objId,$objectIds){
        return new \Google_Service_Slides_Request(array(
            'duplicateObject' => array(
                'objectId' => $objId,
                'objectIds' => $objectIds,
            )
        ));
    }
    /** 
     * return request for update slide position
     * @param array $slideObjectIds
     * @param string $insertionIndex
     * @return Google_Service_Slides_Request
     */
    public static function updateSlidesPosition($slideObjectIds,$insertionIndex){
        printf("update position ");
        echo "\n";
        return new \Google_Service_Slides_Request(array(
            'updateSlidesPosition' => array(
                'slideObjectIds' => $slideObjectIds,
                'insertionIndex' => $insertionIndex
            )
        ));
    }
    /** 
     * return request for insert text into slide 
     * @param string $pageElementId
     * @param string $text
     * @return Google_Service_Slides_Request
     */
    public static function insertText($pageElementId,$text){
        //  insert new text.
        printf("creating Text: {$text} on Page Element: %s", $pageElementId);
        echo "\n";
        return new \Google_Service_Slides_Request(array(
            'insertText' => array(
                'objectId' => $pageElementId,
                'text' => $text,
                'insertionIndex' => 0,
            )
        ));
    }
    /** 
     * return request for replace text into slide
     * @param string $replaceText
     * @param array $pageObjectIds
     * @param string $containsText
     * @return Google_Service_Slides_Request
     */
    public static function replaceAllText($replaceText,$pageObjectIds,$containsText){
        //  replace all text.
        printf("replace Text: {$containsText} to : %s", $replaceText);
        echo "\n";
        return new \Google_Service_Slides_Request(array(
            'replaceAllText' => array(
                'containsText' => array(
                    'text' => $containsText,
                    'matchCase' => true
                ),
                'pageObjectIds' => $pageObjectIds,
                'replaceText' => $replaceText
            )
        ));

    }


    public static function createTableRequest($objectId,$pageObjectId,$rows,$columns){
        return new \Google_Service_Slides_Request(array(
            'createTable' => array(
                'objectId' => $objectId,
                'elementProperties' => array(
                    'pageObjectId' => $pageObjectId,
                ),
                'rows' => $rows,
                'columns' => $columns,
            )
        ));
    }

    public static function updateTableCellPropertiesColorBackground($tableId,$rowIndex,$columnIndex,$rowSpan = 1,$columnSpan,$rgbColor){
        return new \Google_Service_Slides_Request(array(
            'updateTableCellProperties' => array(
                'objectId' => $tableId,
                'tableRange' => array(
                    "location"=> array(
                        "rowIndex"=> $rowIndex,
                        "columnIndex"=> $columnIndex
                    ),
                    "rowSpan" => $rowSpan,
                    "columnSpan"=> $columnSpan
                ), 
                "tableCellProperties"=> array(
                    "tableCellBackgroundFill"=> array(
                        "solidFill" => array(
                            "color" => array(
                                "rgbColor" => $rgbColor
                            ),
                        ),
                    ),
                ),
                "fields"=> 'tableCellBackgroundFill.solidFill.color'
            )
        ));
    }

    public static function updateTextStyleTable($objectId,$rowIndex,$columnIndex,$rgbColor){
        return new \Google_Service_Slides_Request(array(
            'updateTextStyle' => array(
                'objectId' => $objectId,
                'cellLocation' => array(
                    "rowIndex" => $rowIndex,
                    "columnIndex" => $columnIndex
                ), 
                "style" => array(
                    "foregroundColor"=> array(
                      "opaqueColor" => array(
                        "rgbColor" => $rgbColor
                      )
                    )
                ),
                "textRange"=> array(
                    "type"=> "ALL",
                ),
                "fields" => "foregroundColor"
            )
        ));
    }

    public static function updateBoldStyleText($objectId,$rowIndex,$columnIndex, $bold){
        return new \Google_Service_Slides_Request(array(
            'updateTextStyle' => array(
                'objectId' => $objectId,
                'cellLocation' => array(
                    "rowIndex" => $rowIndex,
                    "columnIndex" => $columnIndex
                ), 
                "style" => array(
                    "bold"=> $bold,
                ),
                "textRange"=> array(
                    "type"=> "ALL",
                ),
                "fields" => "bold"
            )
        )); 
    }

    public static function updateLinkText($objectId,$hiperlink){
        return new \Google_Service_Slides_Request(array(
            'updateTextStyle' => array(
                'objectId' => $objectId,
                "style" => array(
                    'link' => array(
                        "url" => $hiperlink,
                    )
                ),
                "fields" => "link"
            )
        )); 
    }
    public static function setHeaderColor($objectId,$rowIndex,$columnIndex){
        $rgbColor = array(
            "red" => "1.0",
            "green" => "1.0",
            "blue" => "1.0"
        );
        return self::updateTextStyleTable($objectId,$rowIndex,$columnIndex,$rgbColor);
    }

    public static function setHeaderColorBackground($tableId,$columnSpan){
        $rgbColor = array(
            "red" => "0.64705884",
            "blue" => "0.20392157"
        );
        return self::updateTableCellPropertiesColorBackground($tableId,$rowIndex = 0,$columnIndex = 0,$rowSpan = 1,$columnSpan,$rgbColor);
    }
    /** 
     * return request for delete text into table in slide
     * @param string $tableId
     * @param integer $rowIndex
     * @param integer $columnIndex
     * @return Google_Service_Slides_Request
     */
    public static function deleteTextCellTable($tableId,$rowIndex,$columnIndex){
        return new \Google_Service_Slides_Request(array(
            'deleteText' => array(
                'objectId' => $tableId,
                'cellLocation' => array(
                    "rowIndex" => $rowIndex,
                    "columnIndex" => $columnIndex
                ), 
                "textRange"=> array(
                    "type"=> "ALL",
                )
            )
        ));
    }

    /** 
     * return request for create text into table in slide
     * @param string $tableId
     * @param integer $rowIndex
     * @param integer $columnIndex
     * @param string $value
     * @return Google_Service_Slides_Request
     */
    public static function insertTextCellTable($tableId,$rowIndex,$columnIndex,$value){
        return new \Google_Service_Slides_Request(array(
            'insertText' => array(
                'objectId' => $tableId,
                'cellLocation' => array(
                    "rowIndex" => $rowIndex,
                    "columnIndex" => $columnIndex
                ), 
                //'insertionIndex' => 0,
                'text' => $value
            )
        ));
    }

    public static function insertTableRows($tableId,$rowIndex = 1,$insertBelow = true,$number){
        return new \Google_Service_Slides_Request(array(
            'insertTableRows' => array(
                'tableObjectId' => $tableId,
                'cellLocation' => array(
                    "rowIndex" => $rowIndex,
                ), 
                'insertBelow' => $insertBelow,
                'number' => $number
            )
        ));
    }


    public static function insertTableColumns($tableId,$rowIndex = 1,$columnIndex = 1,$insertRight = true,$number){
        return new \Google_Service_Slides_Request(array(
            'insertTableColumns' => array(
                'tableObjectId' => $tableId,
                'cellLocation' => array(
                    "rowIndex" => $rowIndex,
                    "columnIndex" => $columnIndex,
                ), 
                'insertRight' => $insertRight,
                'number' => $number
            )
        ));
    }

    public static function updateTableRowProperties($tableId,$dimension){
        return new \Google_Service_Slides_Request(array(
            'updateTableRowProperties' => array(
                'objectId' => $tableId,
                'rowIndices' => [],
                'tableRowProperties' => array(
                    "minRowHeight" => $dimension,
                ), 
                'fields' => "minRowHeight",
            )
        ));
    }

    public static function updateTable($recordsObjects,$tableId,$request){
        
        foreach($recordsObjects as $rowIndex => $recordsObject){
            foreach($recordsObject as $columnIndex => $value){

                $request[] = self::deleteTextCellTable($tableId,$rowIndex,$columnIndex);
                $request[] = self::insertTextCellTable($tableId,$rowIndex,$columnIndex,$value);
                
            }
        }
        return $request;
    }

    public static function replaceAllShapesWithSheetsChart($containsText,$spreadsheetId,$chartId,$pageObjectIds){
        return new \Google_Service_Slides_Request(array(
            'replaceAllShapesWithSheetsChart' => array(
                'containsText'=> array(
                    'text' => $containsText,
                    'matchCase' => true
                ),
                'spreadsheetId' => $spreadsheetId,
                'chartId' => $chartId,
                'linkingMode' => 'LINKED',
                'pageObjectIds' => $pageObjectIds,
            )
        ));
    }
    /**
     * finds in the string a number enclosed in a parenthesis 
     * if it finds it adds 1 to it in order to version the names.
     * @param string $presentationName
     * @return string new string change
     */
    protected static function setVersionName($presentationName){
        $name = explode(" ", trim($presentationName));

        if(count($name) > 1){
            preg_match('!\(([^\)]+)\)!', end($name), $match);
            
            if(count($match) && isset($match[1])){
                if(is_numeric($match[1])){
                    $version = (int) $match[1] + 1; 
                    $name[count($name) -1] = "({$version})";
                    $presentationName = implode(" ",$name);
                }else{
                    $presentationName .= " (1)";
                }
            }else{
                $presentationName .= " (1)";
            }
        }else{
            $presentationName .= " (1)";
        }
        
        return $presentationName;

    }

    /**
     * split string to find the number of slide
     * @param string $slideId
     * @return int index of slide
     */
    public static function getNumberSlideId($slideId){
        $slideId_explode =  explode("slide_",$slideId);
        $number = end($slideId_explode);
        return (int) $number;
    }

    /**
     * return a rando string
     * @param int lenght to string
     * @return string new string change
     */
    public static function randonString($length = 5){
        return substr(str_shuffle('0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ'),1,$length);
    }

    public static function roundout ($value, $places=0) {
        if ($places < 0) { $places = 0; }
        $x= pow(10, $places);
        return ($value >= 0 ? ceil($value * $x):floor($value * $x)) / $x;
    }
}