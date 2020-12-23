<?php
namespace app\helpers;

use yii;
use yii\helpers\FileHelper;
use yii\web\UploadedFile;

use app\models\file\JsonFile;

use PhpOffice\PhpSpreadsheet\IOFactory;
use Box\Spout\Writer\Common\Creator\WriterFactory;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Box\Spout\Common\Entity\Row;
use Box\Spout\Common\Type;
use QuickChart;

/**
 *
 * @author Eduardo Morales <eduardo@montana-studio.com>
 * @group  Montana-Studio LG 
 */

/**
 * FileHelper wrapper for file function.
 *
 */
class DocumentHelper
{
    /**
     * Checks if a document json with the alert id and resource name exists
     * @param  Integer $alertId [id from the alert]
     * @param  String  $resource String [name from the resource]
     * @return Boolean
     */
    public static function isDocumentExist($alertId,$resource){
        $jsonfile = new JsonFile($alertId,$resource);
        return $jsonfile->isDocumentExist();

    }

    /**
     * save data to json file inside a folder with id alert
     * @param  Integer $alertId [id alert]
     * @param  String   $resourcesName
     * @param  Array   $data
     */
    public static function saveJsonFile($alertId,$resourcesName,$data){
        if(!empty($data)){
            // call jsonfile
            $jsonfile = new JsonFile($alertId,$resourcesName);
            $jsonfile->fileName = $alertId;
            $jsonfile->load($data);
            $jsonfile->save();
        }
    }

    /**
     * Move files json to folder with the name folder processed
     * @param  Integer $alertId [id from the alert]
     * @param  String  $resource String [name from the resource]
     */
	public static function moveFilesToProcessed($alertId,$resource){

        $s = DIRECTORY_SEPARATOR;
        $path = \Yii::getAlias('@data')."{$s}{$alertId}{$s}{$resource}{$s}";
        // read the path
        $files = \yii\helpers\FileHelper::findFiles($path,['except'=>['*.php','*.txt'],'recursive' => false]); 
        // create directory
        $folderName = 'processed';
        $create = \yii\helpers\FileHelper::createDirectory("{$path}{$folderName}",$mode = 0777, $recursive = true);
        // move files
        if(isset($files[0])){
            $file = $files[0];
            $split_path = explode("{$s}",$file);
            $fileName = end($split_path);
            try {
                if(copy("{$file}","{$path}{$folderName}{$s}{$fileName}")){
                    try {
                       unlink("{$file}"); 
                    } catch (\yii\base\ErrorException $e) {
                        echo $e->getMessage();
                    }
                }
            } catch (\yii\base\ErrorException $e) {
                echo $e->getMessage();
            }
        }

	}

    /**
     * Move files json to root folder
     * @param  Integer $alertId [id from the alert]
     * @param  String  $resource String [name from the resource]
     */
    public static function moveFilesToRoot($alertId,$resource){
        $s = DIRECTORY_SEPARATOR;
        $folderTarget = 'processed';
        $rootPath = \Yii::getAlias('@data')."{$s}{$alertId}{$s}{$resource}{$s}";
        $pathTarget = \Yii::getAlias('@data')."{$s}{$alertId}{$s}{$resource}{$s}{$folderTarget}{$s}";
        if (is_dir($pathTarget)) {
            $filesTarget = \yii\helpers\FileHelper::findFiles($pathTarget,['except'=>['*.php','*.txt'],'recursive' => false]);
            // move files
            foreach($filesTarget as $file){
                $split_path = explode("{$s}",$file);
                $fileName = end($split_path);
                if(copy("{$file}","{$rootPath}{$s}{$fileName}")){
                    unlink("{$file}");
                }
            } 
        }
    }

    /**
     * get data to excel file to array
     * @param  Object $model [model alert]
     * @param  Array  $attribute
     */
	public static function excelToArray($model,$attribute){
        // https://es.stackoverflow.com/questions/69486/phpexcel-genera-error-allowed-memory-size-of-bytes-exhausted
        ini_set('memory_limit', '2G');
		// is instance of document
		$file = UploadedFile::getInstance($model, $attribute);
		// get extension by the name
        $extension = explode('.', $file->name)[1];
        // create reader
        $reader = IOFactory::createReader(ucfirst($extension));
        // load the document into
        $sheet = $reader->load($file->tempName);
        // convert to array
        $worksheets = $sheet->getActiveSheet()->toArray();
        // delete values null
        $c = function($v){
            return array_filter($v) != array();
        };
        $worksheets = array_filter($worksheets, $c);
        // get headers
        $headers = $worksheets[0];
        $data = [];
        for($w = 1; $w < count($worksheets); $w++){
          for($r = 0; $r < count($worksheets[$w]); $r++){
            $row[$headers[$r]] = $worksheets[$w][$r];
          }
          $data[] = $row;
        }

       return $data;
	}
	
    /**
     * create a file excel 
     * @param  String $filePath [id alert]
     * @param  Array   $data
     */ 
    public static function createExcelDocumentForMentions($filePath,$data){
        $writer = WriterEntityFactory::createXLSXWriter();
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
        /** add a row at a time */
        $singleRow = WriterEntityFactory::createRow($cells);
        $writer->addRow($singleRow);
        
        
        /** Shortcut: add a row from an array of values */
        for ($v=0; $v < sizeOf($data) ; $v++) {
            $rowFromValues = WriterEntityFactory::createRowFromArray($data[$v]);
            $writer->addRow($rowFromValues);
        }
        
        $writer->close();
    }



    public static function actionGraphTermsCountByResourceId($alertId,$resourceId){
        $data = \app\helpers\MentionsHelper::getProductInteration($alertId,$resourceId);
        $terms = [];
        $totals = [];

        $config = [
            'type' => 'bar',
            'data' => [
              'labels' => [],
              'datasets' => [
                [
                  'label' => 'Total',
                  'data'  => [],
                  'backgroundColor' => 'rgba(54, 162, 235, 0.5)'
                ],
              ],
            ],
            'options' => [
            //   'title'=> [
            //     'display'=> true,
            //     'text'=> 'Totales por terminos',
            //     'fontSize'=> 5,
            //    ],
              'plugins' => [
                'datalabels' => [
                  'anchor' => 'center',
                  'align' => 'center',
                  'color' => '#fff',
                  'font' => [
                      'weight' => 'bold'
                  ]
                ]
              ]
            ]
          ];

        for($i = 0; $i < sizeOf($data['data']); $i ++){
            $config['data']['labels'][] = $data['data'][$i][0];
            $config['data']['datasets'][0]['data'][] = $data['data'][$i][3];
        }

        $qc = new \QuickChart(array(
            'width'=> 300,
            'height'=> 280,
        ));


        $config_json = json_encode($config);
        $qc->setConfig($config_json);
        
        # Print the chart URL
        $url =  $qc->getShortUrl();

        return $url;
    }


    public static function actionGraphDataTermsByResourceId($alertId,$resourceId){

        $data = \app\helpers\MentionsHelper::getProductInteration($alertId,$resourceId);
        $terms = [];
        $totals = [];
        
        $labels = [];
        $datasets = [
            [
                'label' => 'total',
                'data'  => [],
                'backgroundColor' => 'rgba(6, 119, 58, 0.5)'
            ],
        ];
        
        
        switch($resourceId){
            case 1: // twitter
                $datasets = [
                    [
                        'label' => 'retweets',
                        'data'  => [],
                        'backgroundColor' => 'rgba(15, 66, 226, 0.5)'
                    ],
                    [
                        'label' => 'favorites',
                        'data'  => [],
                        'backgroundColor' => 'rgba(226, 15, 37, 0.5)'
                    ],
                    [
                        'label' => 'total',
                        'data'  => [],
                        'backgroundColor' => 'rgba(6, 119, 58, 0.5)'
                    ],
                ];
                for($i = 0; $i < sizeOf($data['data']); $i ++){
                    $labels[] = $data['data'][$i][0];
                    $datasets[0]['data'][] = $data['data'][$i][1];
                    $datasets[1]['data'][] = $data['data'][$i][2];
                    $datasets[2]['data'][] = $data['data'][$i][3];
                }
            break;
            case 5: // facebook C
                $datasets = [
                    [
                        'label' => 'shares',
                        'data'  => [],
                        'backgroundColor' => 'rgba(6, 15, 119, 0.5)'
                    ],
                    [
                        'label' => 'total',
                        'data'  => [],
                        'backgroundColor' => 'rgba(6, 119, 58, 0.5)'
                    ],
                ];
                for($i = 0; $i < sizeOf($data['data']); $i ++){
                    $labels[] = $data['data'][$i][0];
                    $datasets[0]['data'][] = $data['data'][$i][1];
                    $datasets[1]['data'][] = $data['data'][$i][3];
                }
            break;

            case 6: // instagram C
                $datasets = [
                    [
                        'label' => 'likes',
                        'data'  => [],
                        'backgroundColor' => 'rgba(226, 15, 37, 0.5)'
                    ],
                    [
                        'label' => 'total',
                        'data'  => [],
                        'backgroundColor' => 'rgba(6, 119, 58, 0.5)'
                    ],
                ];
                for($i = 0; $i < sizeOf($data['data']); $i ++){
                    $labels[] = $data['data'][$i][0];
                    $config['data']['datasets'][0]['data'][] = $data['data'][$i][2];
                    $config['data']['datasets'][1]['data'][] = $data['data'][$i][3];
                }
            break;
            
            default: // livechat , scraping
            for($i = 0; $i < sizeOf($data['data']); $i ++){
                    $labels[] = $data['data'][$i][0];
                    $datasets[0]['data'][] = $data['data'][$i][3];
                }
            break;

        }

        
        $config = [
            'type' => 'bar',
            'data' => [
              'labels' => $labels,
              'datasets' => $datasets,
            ],
            'options' => [
            
              'plugins' => [
                'datalabels' => [
                  'anchor' => 'center',
                  'align' => 'center',
                  'color' => '#fff',
                  'font' => [
                      'weight' => 'bold'
                  ]
                ]
              ]
            ]
          ];
        

        $qc = new \QuickChart(array(
            'width'=> 300,
            'height'=> 280,
        ));


        $config_json = json_encode($config);
        $qc->setConfig($config_json);
        
        # Print the chart URL
        $url =  $qc->getShortUrl();

        return $url;
    }


    
}