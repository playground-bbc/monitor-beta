<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\commands;

use yii\console\Controller;
use yii\console\ExitCode;
/**
 *
 * This command is provided creation document
 */
class DocumentController extends Controller
{
    /**
     * This command echoes what you have entered as the message.
     * @param string $message the message to be echoed.
     * @return int Exit code
     */
    public function actionIndex()
    {   
      $alert = new \app\models\Alerts();
      $alertsConfig = $alert->getBringAllAlertsToRun(true);
      
      if(!empty($alertsConfig)){
        // loop alerts
        foreach ($alertsConfig as $index => $alerts) {
          // if there mentions 
          $alertsMentions = \app\helpers\AlertMentionsHelper::getAlersMentions(['alertId' => $alerts['id']]);
          if(!is_null($alertsMentions)){
            // get alertMentios ids
            $alertsMentionsIds = \yii\helpers\ArrayHelper::getColumn($alertsMentions, 'id');
            // get mentions order by created_at
            $mentions = \app\models\Mentions::find()->select('createdAt')->where(['alert_mentionId' => $alertsMentionsIds])->orderBy(['createdAt' => SORT_ASC])->asArray()->all();
            // if there mentions
            if(count($mentions)){
              // recent registration
              $record = end($mentions);
              $createdAt = $record['createdAt'];
              // if dir folder
              $pathFolder = \Yii::getAlias("@runtime/export/{$alerts['id']}");
              $fileIsCreated = false;
              if (!is_dir($pathFolder)){
                // set path folder options
                $folderOptions = [
                  'path' => \Yii::getAlias('@runtime/export/'),
                  'name' => $alerts['id'],
                ];
                // create folder
                $folderPath = \app\helpers\DirectoryHelper::setFolderPath($folderOptions);
                $fileIsCreated = true;
              }else{
                $files = \yii\helpers\FileHelper::findFiles($pathFolder,['only'=>['*.xlsx','*.xls']]);
                // get the name the file
                if(isset($files[0])){
                  $path_explode = explode('/',$files[0]);
                  $filename = explode('.',end($path_explode));
                  
                  if($filename[0] != $createdAt){
                    unlink($files[0]);
                    $fileIsCreated = true; 
                  }

                }else{
                  $fileIsCreated = true; 
                }

              }

              if($fileIsCreated){
                $data =  \app\helpers\MentionsHelper::getDataMentions($alerts['id']);
                if(count($data)){
                  $folderPath = \Yii::getAlias("@runtime/export/{$alerts['id']}/");
                  $filePath = $folderPath."{$createdAt}.xlsx";
                  \app\helpers\DocumentHelper::createExcelDocumentForMentions($filePath,$data);  
                }
              }
            }
          }   
        }

      }
      return ExitCode::OK;
    }
}
