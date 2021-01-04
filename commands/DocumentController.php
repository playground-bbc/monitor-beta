<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\commands;

use yii\console\Controller;
use yii\console\ExitCode;
use \kartik\mpdf\Pdf;
/**
 *
 * This command is provided creation document
 */
class DocumentController extends Controller
{
    /**
     * This command create document excel with mention of the alert.
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

    
    /**
     * This command create json with post facebook.
     * @return int Exit code
     */
    public function actionFacebookPost()
    {
        // get credential user
        $userFacebook = \app\helpers\FacebookHelper::getUserActiveFacebook();

        $secret_proof = \app\helpers\FacebookHelper::getAppsecretProof($userFacebook['credencial']['access_token']);
        // set params facebook
        $_baseUrl = 'https://graph.facebook.com/v6.0';
        $until_start_date = 1572393610; // 30 October 2019 0:00:10
        $since_end_date = 1569888010; //  1 October 2019 0:00:10
        $limit = 25;
        $params = ['access_token' => $userFacebook['credencial']['access_token'], 'appsecret_proof' => $secret_proof];
        $busines_id = 169441517247;
        //$end_point = "{$busines_id}/published_posts?fields=id,permalink_url,created_time,message,insights.metric(post_impressions,post_engaged_users,post_reactions_by_type_total,page_actions_post_reactions_total)&until={$until_start_date}&since={$since_end_date}&limit={$limit}";
        $end_point = "{$busines_id}/published_posts?fields=id,permalink_url,created_time,message,insights.metric(post_impressions_paid,post_impressions_fan_paid,post_impressions_organic,post_impressions,post_engaged_users,post_reactions_by_type_total,page_actions_post_reactions_total)&until={$until_start_date}&since={$since_end_date}&limit={$limit}";
        //$end_point = "{$busines_id}/published_posts?fields=id,permalink_url,created_time,message,insights.metric(post_impressions_paid,post_impressions_fan_paid,post_impressions_organic,post_impressions,post_engaged_users,post_reactions_by_type_total,page_actions_post_reactions_total)&until={$until_start_date}&limit={$limit}";
        // try to call api and get data
        $after = '';
        $index = 0;
        $data = null;
        $responseData = [];
        $client = new \yii\httpclient\Client(['baseUrl' => $_baseUrl]);

        do
        {
            try
            {
                $response = $client->get($end_point, $params)->setOptions(['timeout' => 10, // set timeout to 10 seconds for the case server is not responding
                ])
                    ->send();

                // if get error data
                if (\yii\helpers\ArrayHelper::getValue($response->getData() , 'error', false))
                {
                    // send email with data $responseData[$index]['error']['message']
                    var_dump($response->getData());
                    break;
                }

                // is over the limit
                $responseHeaders = $response
                    ->headers
                    ->get('x-business-use-case-usage'); // get headers
                if (\app\helpers\FacebookHelper::isCaseUsage($responseHeaders))
                {
                    break;
                }

                // get the after
                if (\yii\helpers\ArrayHelper::getValue($response->getData() , 'paging.cursors.after', false))
                { // if next
                    $after = \yii\helpers\ArrayHelper::getValue($response->getData() , 'paging.cursors.after', false);
                    echo $after . "\n";
                    $params['after'] = $after;
                    $is_next = true;
                }
                else
                {
                    $is_next = false;
                }
                $data = $response->getData(); // get all post and comments
                if (isset($data['data']))
                {
                    if (!empty($data['data']))
                    {
                        $responseData[$index] = $data;
                        $index++;
                    }
                    else
                    {
                        $is_next = false;
                    }
                }
                else
                {
                    $is_next = false;
                }
            }
            catch(\yii\httpclient\Exception $e)
            {
                // send email
                
            }
        }
        while ($is_next);

        //save data
        if (!is_null($responseData))
        {
            $jsonfile = new \app\models\file\JsonFile(45, "insigths");
            $jsonfile->fileName = 'Facebook';
            $jsonfile->load($responseData);
            $jsonfile->save();
        }
    }
    /**
     * This command create json with post instagram.
     * @return int Exit code
     */
    public function actionInstagramPost()
    {
        // get credential user
        $userFacebook = \app\helpers\FacebookHelper::getUserActiveFacebook();
        $secret_proof = \app\helpers\FacebookHelper::getAppsecretProof($userFacebook['credencial']['access_token']);
        // set params instagram
        $_baseUrl = 'https://graph.facebook.com/v6.0';
        $until_start_date = 1572393610; // 30 October 2019 0:00:10
        $since_end_date = 1569888010; //  1 October 2019 0:00:10
        $limit = 25;
        $params = ['access_token' => $userFacebook['credencial']['access_token'], 'appsecret_proof' => $secret_proof];
        $busines_id = 17841400255273872;
        $end_point = "{$busines_id}/media?fields=id,permalink,timestamp,caption,insights.metric(impressions,reach,engagement)&limit={$limit}";

        // try to call api and get data
        $after = '';
        $index = 0;
        $data = null;
        $responseData = [];
        $client = new \yii\httpclient\Client(['baseUrl' => $_baseUrl]);

        do
        {
            try
            {
                $response = $client->get($end_point, $params)->setOptions(['timeout' => 10, // set timeout to 10 seconds for the case server is not responding
                ])
                    ->send();

                $responseHeaders = $response
                    ->headers
                    ->get('x-business-use-case-usage'); // get headers
                

                // if get error data
                if (\yii\helpers\ArrayHelper::getValue($response->getData() , 'error', false))
                {
                    // send email with data $responseData[$index]['error']['message']
                    break;
                }

                // is over the limit
                if (\app\helpers\FacebookHelper::isCaseUsage($responseHeaders, $busines_id))
                {
                    break;
                }

                // get the after
                if (\yii\helpers\ArrayHelper::getValue($response->getData() , 'paging.cursors.after', false))
                { // if next
                    $after = \yii\helpers\ArrayHelper::getValue($response->getData() , 'paging.cursors.after', false);
                    echo $after . "\n";
                    $params['after'] = $after;
                    $is_next = true;
                }
                else
                {
                    $is_next = false;
                }

                $data = $response->getData(); // get all insights
                if (isset($data['data'][0]['timestamp']))
                {
                    $date_post = strtotime($data['data'][0]['timestamp']);
                    echo $data['data'][0]['timestamp'] . "\n";

                    if (($date_post >= $until_start_date))
                    {
                        $responseData[$index] = $data;
                        $index++;
                    }
                    else
                    {
                        $is_next = false;
                    }
                }
                else
                {
                    echo "is break";
                    $is_next = false;
                }
            }
            catch(\yii\httpclient\Exception $e)
            {
                // send email
                
            }
        }
        while ($is_next);

        //save data
        if (!is_null($responseData))
        {
            $jsonfile = new \app\models\file\JsonFile(45, "insigths");
            $jsonfile->fileName = 'Instagram';
            $jsonfile->load($responseData);
            $jsonfile->save();
        }
    }
}

