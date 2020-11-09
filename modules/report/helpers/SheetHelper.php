<?php
namespace app\modules\report\helpers;

use yii;
use yii\db\Expression;
use yii\helpers\Console;
require_once Yii::getAlias('@vendor') . '/autoload.php'; // call google client

/**
 *
 * @author Eduardo Morales <eduardo@montana-studio.com>
 * @group  Montana-Studio LG 
 */


class SheetHelper{

    public static function getIdFromUrl($url){
        $spreadsheetId = null;
        // get id spreadsheetId
        if(strlen(rtrim($url, "\n\r"))){
            $url_array = explode("/",$url);
            $spreadsheetId = $url_array[count($url_array) -2];
        }
        return $spreadsheetId;
    }

    public static function getValuesFromRange($client,$spreadsheetId,$range){
       
        $service = new \Google_Service_Sheets($client);
        $response = $service->spreadsheets_values->get($spreadsheetId, $range);
        return $response->getValues();
    }

    public static function getSheetIdByName($client,$spreadsheetId,$sheetName){
        
        $service = new \Google_Service_Sheets($client);
        $response = $service->spreadsheets->get($spreadsheetId);
        $sheetId = null;
        foreach($response->sheets as $indexSheet => $sheet){
            $title_sheet = $response->sheets[$indexSheet]->properties->title;
            if($sheetName == $title_sheet){
                $sheetId = $response->sheets[$indexSheet]->properties->sheetId;
            }
        }
        return $sheetId;
    }

    public static function getSheetsName($client,$spreadsheetId){
        
        $service = new \Google_Service_Sheets($client);
        $response = $service->spreadsheets->get($spreadsheetId);
        $sheetName = [];
        foreach($response->sheets as $indexSheet => $sheet){
            $sheetName[] = $response->sheets[$indexSheet]->properties->title;
            
        }
        return $sheetName;
    }

    public static function getChartsBySheetID($client,$spreadsheetId){
        $service = new \Google_Service_Sheets($client);
        
        $response = $service->spreadsheets->get($spreadsheetId);
        $data = [];
        foreach($response->sheets  as $indexResponse => $sheet){
            if(count($sheet->getCharts())){
                foreach($sheet->getCharts() as $indexChart => $chart){
                    $chartId =  $chart->getChartId();
                    $title   =  $chart->getSpec()->getTitle();
                    $pieChart = $chart->getSpec()->getPieChart();
                    $sources = $chart->getSpec()->getPieChart()->getSeries()->getSourceRange()->getSources();
                    if(count($sources)){
                        $sheetId = $sources[0]['sheetId'];
                        $data[$sheetId][] = [
                            'title' => $title,
                            'chartId' => $chartId,
                            'series' => [
                                'startRowIndex' => $sources[0]['startRowIndex'],
                                'endRowIndex' => $sources[0]['endRowIndex'],
                                'startColumnIndex' => $sources[0]['startColumnIndex'],
                                'endColumnIndex' => $sources[0]['endColumnIndex'],
                            ],
                        ];
                    }
                }
            }

        }
        return $data;
    }


    public static function setRangeBySourcesSeries($sheetName,$series){
        $columns = ['A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P'];
        
        $startColumnIndex = $columns[$series['startColumnIndex'] - 1]; //B
        $startRowIndex = $series['startRowIndex'] + 1; //3

        $endColumnIndex = $columns[$series['endColumnIndex'] - 1]; //C
        $endRowIndex = $series['endRowIndex'];//8
        $range = "{$sheetName}!{$startColumnIndex}{$startRowIndex}:{$endColumnIndex}{$endRowIndex}";

        return $range;
    }

    public static function sanitazeStringValuesNullAndUpperFirtsLetter($value){
        $target_null = ['-','No muestra información Sprinklr'];
        if(!is_numeric($value)){
            if(in_array($value,$target_null)){
                $value = 'Sin Info Sprinklr';
            }{
                $value = ucfirst($value);
                
            }
        }
        
        return $value;
    }

    public static function filterViewByProperties($client,$spreadsheetId){
        
        $body = new \Google_Service_Sheets_BatchUpdateSpreadsheetRequest(
            array(
                'requests' => array(
                    'addFilterView' => array(
                        'filter' => array(
                            'title' => 'Sample Filter',
                            'range' => array(
                                'sheetId' => '772570429',
                                'startRowIndex' => 1,
                                'startColumnIndex' => 2,
                            ),
                            'sortSpecs' => array(
                                [
                                    'dimensionIndex'=> 7,
                                    'sortOrder'=> 'DESCENDING',
                                ]
                            ),
                        )
                    )
                )
            )
        );
        $service = new \Google_Service_Sheets($client);
        $response = $service->spreadsheets->batchUpdate($spreadsheetId, $body);
        print_r($response);
    }


    public static function setBasicFilter($client,$spreadsheetId){
        $requests = [
            new \Google_Service_Sheets_Request( array(
                'sortRange' => array(
                    'range' => array(
                        'sheetId' => '1474413006',
                        'startRowIndex' => '3',
                        'startColumnIndex' => '3'
                    ),
                    'sortSpecs' => array(
                        [
                            'dimensionIndex'=> 3,
                            'sortOrder'=> 'DESCENDING',
                        ]
                    ),
                )
              )
          )
        ];  
        $batchUpdateRequest = new \Google_Service_Sheets_BatchUpdateSpreadsheetRequest(['requests' => $requests]);
        $service = new \Google_Service_Sheets($client);
        $response = $service->spreadsheets->batchUpdate($spreadsheetId, $batchUpdateRequest);
        print_r($response);
        
    }


    public static function getRecordOrderBy($recordsObject,$order){
        $valuesName = \yii\helpers\ArrayHelper::remove($recordsObject, 0);
        $valuesName[8] = 'Topic';
        
        $records = [];
        foreach($recordsObject as $recordIndexObject => $recordObject){
            foreach($recordObject as $recordIndex => $value){

                $records[$recordIndexObject][$valuesName[$recordIndex]] = $value;
            }
        }
        $name = $order[0];
        $order_type =  $order[1];
        \yii\helpers\ArrayHelper::multisort($records, $name, $order_type);
        return $records;
    }

    public static function getRecordsWhere($records,$where){
        $record = [];
        $key = key($where);
        $value = $where[$key];
        for ($i=0; $i < sizeOf($records) ; $i++) { 
            if($records[$i][$key] == $value || $records[$i][$key] == strtoupper($value) || $records[$i][$key] == ucfirst($value)){
                $permalink = $records[$i]['Permalink'];
                $domain = self::getDomain($permalink);
               
                if($domain != 'youtube.com'){
                    $records[$i]['domain'] = $domain;
                    $record[rtrim($domain,'.com')][] = $records[$i];
                }
            }
        }
        \yii\helpers\ArrayHelper::multisort($record, $key, SORT_DESC);
        return $record;
    }


    /**
     * [getDomain get domain form url]
     * @param  [string] $url
     * @return [string] 
     */
    public static function getDomain($url){
        $pieces = parse_url($url);
        $domain = isset($pieces['host']) ? $pieces['host'] : '';
        if(preg_match('/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i', $domain, $regs)){
            return $regs['domain'];
        }
        return FALSE;
    }

    public static function addValuesOfTheGraphs($values){
        $total = 0;
        for ($v=0; $v < sizeOf($values) ; $v++) { 
            for ($r=0; $r < sizeOf($values[$v]) ; $r++) { 
                if(is_numeric($values[$v][$r])){
                    $total += $values[$v][$r];
                }
            }
        }

        return (string) $total;
    }
}