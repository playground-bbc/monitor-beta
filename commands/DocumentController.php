<?php
namespace app\commands;

use yii\console\Controller;
use yii\console\ExitCode;


/**
 * This command will runs all report generations
 *
 * @author Eduardo Morales <eduardo@montana-studio.com>
 */
class DocumentController extends Controller
{
    /**
     * This command (DEFAULT) generates the excel file with all the mentions collected in each alert
     * @return int Exit code
     */
    public function actionIndex()
    {
        $alert = new \app\models\Alerts();
        $alertsConfig = $alert->getBringAllAlertsToRun(true);

        if (!empty($alertsConfig))
        {
            // loop alerts
            foreach ($alertsConfig as $index => $alerts)
            {
                // if there mentions
                $alertsMentions = \app\helpers\AlertMentionsHelper::getAlersMentions(['alertId' => $alerts['id']]);
                if (!is_null($alertsMentions))
                {
                    // get alertMentios ids
                    $alertsMentionsIds = \yii\helpers\ArrayHelper::getColumn($alertsMentions, 'id');
                    // get mentions order by created_at
                    $mentions = \app\models\Mentions::find()->select('createdAt')
                        ->where(['alert_mentionId' => $alertsMentionsIds])->orderBy(['createdAt' => SORT_ASC])
                        ->asArray()
                        ->all();
                    // if there mentions
                    if (count($mentions))
                    {
                        // recent registration
                        $record = end($mentions);
                        $createdAt = $record['createdAt'];
                        // if dir folder
                        $pathFolder = \Yii::getAlias("@runtime/export/{$alerts['id']}");
                        $fileIsCreated = false;
                        if (!is_dir($pathFolder))
                        {
                            // set path folder options
                            $folderOptions = ['path' => \Yii::getAlias('@runtime/export/') , 'name' => $alerts['id']];
                            // create folder
                            $folderPath = \app\helpers\DirectoryHelper::setFolderPath($folderOptions);
                            $fileIsCreated = true;
                        }
                        else
                        {
                            $files = \yii\helpers\FileHelper::findFiles($pathFolder, ['only' => ['*.xlsx', '*.xls']]);
                            // get the name the file
                            if (isset($files[0]))
                            {
                                $path_explode = explode('/', $files[0]);
                                $filename = explode('.', end($path_explode));

                                if ($filename[0] != $createdAt)
                                {
                                    unlink($files[0]);
                                    $fileIsCreated = true;
                                }
                            }
                            else
                            {
                                $fileIsCreated = true;
                            }
                        }

                        if ($fileIsCreated)
                        {
                            $data = \app\helpers\MentionsHelper::getDataMentions($alerts['id']);
                            if (count($data))
                            {
                                $folderPath = \Yii::getAlias("@runtime/export/{$alerts['id']}/");
                                $filePath = $folderPath . "{$createdAt}.xlsx";
                                \app\helpers\DocumentHelper::createExcelDocumentForMentions($filePath, $data);
                            }
                        }
                    }
                }
            }
        }
        return ExitCode::OK;
    }

    /**
     * This command create document pdf of the alert.
     * @return int Exit code
     */
    public function actionPdf()
    {
        $alerts = \app\models\Alerts::find()->all();

        if (!empty($alerts))
        {
            // loop alerts
            foreach ($alerts as $index => $alert)
            {
                // if there mentions
                $alertsMentions = \app\helpers\AlertMentionsHelper::getAlersMentions(['alertId' => $alert->id]);
                if (!is_null($alertsMentions))
                {
                    // get alertMentios ids
                    $alertsMentionsIds = \yii\helpers\ArrayHelper::getColumn($alertsMentions, 'id');
                    // get mentions order by created_at
                    $mentions = \app\models\Mentions::find()->select('createdAt')
                        ->where(['alert_mentionId' => $alertsMentionsIds])->orderBy(['createdAt' => SORT_ASC])
                        ->asArray()
                        ->all();
                    // if there mentions
                    if (count($mentions))
                    {
                        // recent registration
                        $record = end($mentions);
                        $createdAt = $record['createdAt'];
                        // if dir folder
                        $pathFolder = \Yii::getAlias("@pdf/{$alert->id}");
                        $fileIsCreated = false;
                        if (!is_dir($pathFolder))
                        {
                            // set path folder options
                            $folderOptions = ['path' => \Yii::getAlias('@pdf/') , 'name' => $alert->id];
                            // create folder
                            $folderPath = \app\helpers\DirectoryHelper::setFolderPath($folderOptions);
                            $fileIsCreated = true;
                        }
                        else
                        {
                            $files = \yii\helpers\FileHelper::findFiles($pathFolder, ['only' => ['*.pdf']]);
                            // get the name the file
                            if (isset($files[0]))
                            {
                                $path_explode = explode('/', $files[0]);
                                $filename = explode('.', end($path_explode));

                                if ($filename[0] != $createdAt)
                                {
                                    unlink($files[0]);
                                    $fileIsCreated = true;
                                }
                            }
                            else
                            {
                                $fileIsCreated = true;
                            }
                        }

                        if ($fileIsCreated)
                        {
                            $resourcesSocialData = \app\helpers\PdfHelper::getDataForPdf($alert); 
                            if (count($resourcesSocialData))
                            {
                                $folderPath = \Yii::getAlias("@pdf/{$alert->id}/");
                                $filePath = $folderPath . "{$createdAt}.pdf";

                                 // load images
                                $url_logo_small = \yii\helpers\Url::to('web/img/logo_small.png',true);
                                $url_logo = \yii\helpers\Url::to('web/img/logo.png',true);
                                
                                $html = $this->renderPartial('_document',[
                                    'model' => $alert,
                                    'resourcesSocialData' => $resourcesSocialData,
                                    'url_logo_small' => $url_logo_small,
                                    'url_logo' =>$url_logo,
                                ]);

                                $pdf = \app\helpers\PdfHelper::getKartikMpdf($filePath,$html,$alert);
                                //$pdf->in_charset='UTF-8';
                                // return the pdf output as per the destination setting
                                $pdf->render(); 
                                unset($pdf);
                            }
                        }
                    }
                }
            }
        }
        return ExitCode::OK;
    }

    
}

