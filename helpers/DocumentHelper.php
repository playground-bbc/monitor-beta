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
    public static function isDocumentExist($alertId,$resource){
        $jsonfile = new JsonFile($alertId,$resource);
        return $jsonfile->isDocumentExist();

    }

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

	public static function saveJsonFile($alertId,$resourcesName,$data){
		if(!empty($data)){
			// call jsonfile
			$jsonfile = new JsonFile($alertId,$resourcesName);
            $jsonfile->fileName = $alertId;
			$jsonfile->load($data);
			$jsonfile->save();
		}
    }
    
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
}