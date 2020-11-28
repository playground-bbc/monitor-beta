<?php

namespace app\commands;

use yii\console\Controller;
use yii\console\ExitCode;
use app\models\Dictionaries;
use yii\helpers\Console;
use QuickChart;

/**
 *
 * This command is provided as email.
 *
 */
class EmailController extends Controller
{
    private $resourceProperties = [
        'Twitter' => [
            'alias' => 'Twitter',
            'background' => 'fdb45c'
        ],
        'Live Chat' => [
            'alias' => 'L.C Tickets', 
            'background' => '5cfdf0',
        ],
        'Live Chat Conversations' => [
            'alias' => 'L.C Chats', 
            'background' => '5cc2fd',
        ],
        'Facebook Comments' => [
            'alias' => 'Facebook Comentarios', 
            'background' => '945cfd',
        ],
        'Facebook Messages' => [
            'alias' => 'Facebook Inbox', 
            'background' => 'd75cfd',
        ],
        'Instagram Comments' => [
            'alias' => 'Instagram Comentarios', 
            'background' => 'fd5cfd',
        ],
        'Paginas Webs' => [
            'alias' => 'Paginas Webs', 
            'background' => 'fd5c5c',
        ],

    ]; 
    /**
     * This command echoes what you have entered as the message.
     * @param string $message the message to be echoed.
     * @return int Exit code
     */
    public function actionIndex()
    {   
        return ExitCode::OK;
    }

    /**
     * This command send email with insights account client.
     * @return int Exit code
     */
    public function actionInsights(){
        $model = [];
        // get resources ids
        $resourcesIds = \app\helpers\InsightsHelper::getNumbersContent();
        if(count($resourcesIds)){
            foreach($resourcesIds as $resourceIndex => $resourceId){
                if(isset($resourceId['resource_id'])){
                    $id = $resourceId['resource_id'];
                    $page = \app\helpers\InsightsHelper::getContentPage($id);
                    $posts_content = \app\helpers\InsightsHelper::getPostsInsights($id);
                    $posts_insights = \app\helpers\InsightsHelper::getPostInsightsByResource($posts_content,$resourceId);
                    $stories = \app\helpers\InsightsHelper::getStorysInsights($id);
                    $page['posts'] = $posts_insights;
                    $page['stories'] = $stories;
                    $model[] = $page;
                }
            }
            
        }
        if(count($model)){
            $pathLogo = dirname(__DIR__)."/web/img/";
            $userEmails = \app\models\Users::find()->select('email')->where(['status' => 10])->all();
            $emails = [];
            foreach ($userEmails as $userEmail) {
                $emails[] = $userEmail->email;
            }
            \Yii::$app->mailer->compose('insights',['model' => $model,'pathLogo' => $pathLogo])
            ->setFrom('monitormtg@gmail.com')
            ->setTo($emails)->setSubject("Insigths de la Cuenta 📝: Mundo Lg")->send();
        }

        return ExitCode::OK;
    }

    /**
     * This command send email with allerts data to client.
     * @return int Exit code
     */
    public function actionAlerts(){
      $alert = new \app\models\Alerts();
      $alertsConfig = $alert->getBringAllAlertsToRun(true,'');
      //loop alerts
      foreach($alertsConfig as $indexAlertsConfig => $alertConfig){
        // get model the user
        $userModel = \app\models\Users::find()->select('email')->where(['id' => $alertConfig['userId']])->one();
        // get info form alert
        $alertId = $alertConfig['id'];
        $alertName = $alertConfig['name'];
        $createdAt = \Yii::$app->formatter->asDatetime($alertConfig['createdAt']);
        $start_date = \Yii::$app->formatter->asDatetime($alertConfig['config']['start_date']);
        $end_date = \Yii::$app->formatter->asDatetime($alertConfig['config']['end_date']);
        $status =  ($alertConfig['status']) ? 'Activa': 'Inactiva';

        $count = (new \yii\db\Query())
        ->cache(10)
        ->from('alerts_mencions')
        ->join('JOIN', 'mentions', 'mentions.alert_mentionId = alerts_mencions.id')
        ->where(['alertId' => $alertId])
        ->count();
        
        
        if($count > 0){
          $sourcesMentionsCount = \app\helpers\MentionsHelper::getCountSourcesMentions($alertId);
            
          if(isset($sourcesMentionsCount['data'])){
            // link graph
            $urlTotalResource = $this->getTotalResourceHyperLinkGraph($sourcesMentionsCount['data']);
            $urlIterationResource = $this->getIterationResourcesHyperLinkGraph($sourcesMentionsCount['data']);
            
            $productsMentionsCount = \app\helpers\MentionsHelper::getProductInteration($alertId);
            $urlIterationsProducts = $this->getIterarionByProductsLinkGraph($productsMentionsCount['data']);

            $message = \Yii::$app->mailer->compose('alerts',[
              'alertId' => $alertId,
              'alertName' => $alertName,
              'createdAt' => $createdAt,
              'start_date' => $start_date,
              'end_date' => $end_date,
              'status' => $status,
              'hiperLinkTotalResource' => $urlTotalResource,
              'hiperLinkIterationResource' => $urlIterationResource,
              'hiperLinkIterationByProducts' => $urlIterationsProducts,
              'frontendUrl' => \Yii::$app->params['frontendUrl'],
            ])
            ->setFrom('monitormtg@gmail.com')
            ->setTo(["spiderbbc@gmail.com"])->setSubject("Alerta Monitor 📝: Mundo Lg");
            $pathFolder = \Yii::getAlias('@runtime/export/').$alertId;
            $isFileAttach = false;
            if(is_dir($pathFolder)){
                $files = \yii\helpers\FileHelper::findFiles($pathFolder,['only'=>['*.xlsx','*.xls']]);
                if(isset($files[0])){
                    $start_date = \Yii::$app->formatter->asDatetime($alertConfig['config']['start_date'],'yyyy-MM-dd');
                    $end_date   = \Yii::$app->formatter->asDatetime($alertConfig['config']['end_date'],'yyyy-MM-dd');
                    $name       = "{$alertConfig['name']} {$start_date} to {$end_date} mentions"; 
                    $file_name  =  \app\helpers\StringHelper::replacingSpacesWithUnderscores($name);

                    $folderPath = \Yii::getAlias("@runtime/export/{$alertId}/");
                    $filePath = $folderPath."{$file_name}.xlsx";
                    copy($files[0],"{$folderPath}{$file_name}.xlsx");
                    // zip files
                    $zip = new \ZipArchive;
                    if ($zip->open($folderPath."{$file_name}.zip", \ZipArchive::CREATE) === TRUE){
                        // Add files to the zip file
                        $zip->addFile("{$folderPath}{$file_name}.xlsx","alert/{$file_name}.xlsx");
                        // All files are added, so close the zip file.
                        $zip->close();
                        // Adjunta un archivo del sistema local de archivos:
                        $message->attach("{$folderPath}{$file_name}.zip");
                        $isFileAttach = true;
                    }
                    
                }
            };
              
            // send email
            $message->send(); 

            if($isFileAttach){
                unlink($filePath);
                unlink("{$folderPath}{$file_name}.zip");
            } 
          }

        }
          
      }
          
      return ExitCode::OK;
    }
    /**
     *  convert the data into a url for the chart total data by resource ej Facebook : 15
     *  @param array $sourcesMentionsCount 
     *  @return string [ or null]
     */
    private function getTotalResourceHyperLinkGraph($sourcesMentionsCount){
        
      $data = $sourcesMentionsCount;
      $url = null;
      
      if(count($data)){
        $qc = new \QuickChart(array(
            'width'=> 550,
            'height'=> 300,
        ));

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
            'plugins' => [
              'datalabels' => [
                'anchor' => 'end',
                'align' => 'top',
                'color' => '#000000',
                'formatter' => '(value) => { return value ;}'
              ]
            ]
          ]
        ];
        

        for($d = 0; $d < sizeOf($data); $d++){
          $config['data']['labels'][] = $data[$d][0];
          $config['data']['datasets'][0]['data'][] = $data[$d][3];
        }
        $config_json = json_encode($config);
        $qc->setConfig($config_json);
        
        # Print the chart URL
        $url =  $qc->getUrl();
      }

      return $url;
    }

    /**
     *  convert the data into a url for the chart Iteration by resource
     *  @param array $sourcesMentionsCount 
     *  @return string [ or null]
     */
    private function getIterationResourcesHyperLinkGraph($sourcesMentionsCount){
      $data = $sourcesMentionsCount;
      $url = null;
      
      if(count($data)){
        $qc = new \QuickChart(array(
            'width'=> 550,
            'height'=> 300,
        ));

        $config = [
          'type' => 'bar',
          'data' => [
            'labels' => [],
            'datasets' => [
              [
                'label' => 'Shares/Retweets',
                'data'  => [],
                'backgroundColor' => 'rgba(54, 162, 235, 0.5)'
              ],
              [
                'label' => 'Likes',
                'data'  => [],
                'backgroundColor' => 'rgba(240, 52, 52, 1)'
              ],
              [
                'label' => 'Total',
                'data'  => [],
                'backgroundColor' => 'rgba(42, 187, 155, 1)'
              ],
            ],
          ],
          'options' => [
            'plugins' => [
              'datalabels' => [
                'anchor' => 'end',
                'align' => 'top',
                'color' => '#000000',
                'formatter' => '(value) => { return value ;}'
              ]
            ]
          ]
        ];
        

        for($d = 0; $d < sizeOf($data); $d++){
          $config['data']['labels'][] = $data[$d][0];
          $config['data']['datasets'][0]['data'][] = $data[$d][1];
          $config['data']['datasets'][1]['data'][] = $data[$d][2];
          $config['data']['datasets'][2]['data'][] = $data[$d][3];
        }
        $config_json = json_encode($config);
        $qc->setConfig($config_json);
        
        # Print the chart URL
        $url =  $qc->getUrl();
      }
      return $url;  
        
    }
    /**
     *  convert the data into a url for the chart Iteration by products
     *  @param array $productsMentionsCount 
     *  @return string [ or null]
     */
    private function getIterarionByProductsLinkGraph($productsMentionsCount){
        
      $data = $productsMentionsCount;
      $url = null;
      if(count($data)){
        $qc = new \QuickChart(array(
            'width'=> 550,
            'height'=> 300,
        ));

        $config = [
          'type' => 'bar',
          'data' => [
            'labels' => [],
            'datasets' => [
              [
                'label' => 'Shares/Retweets',
                'data'  => [],
                'backgroundColor' => 'rgba(54, 162, 235, 0.5)'
              ],
              [
                'label' => 'Likes',
                'data'  => [],
                'backgroundColor' => 'rgba(240, 52, 52, 1)'
              ],
              [
                'label' => 'Total',
                'data'  => [],
                'backgroundColor' => 'rgba(42, 187, 155, 1)'
              ],
            ],
          ],
          'options' => [
            'plugins' => [
              'datalabels' => [
                'anchor' => 'end',
                'align' => 'top',
                'color' => '#000000',
                'formatter' => '(value) => { return value ;}'
              ]
            ]
          ]
        ];
        

        $limit = 3;
        for($d = 0; $d < sizeOf($data); $d++){
          if($d < $limit){
            $config['data']['labels'][] = $data[$d][0];
            $config['data']['datasets'][0]['data'][] = $data[$d][1];
            $config['data']['datasets'][1]['data'][] = $data[$d][2];
            $config['data']['datasets'][0]['data'][] = $data[$d][3];
          }
        }
        $config_json = json_encode($config);
        $qc->setConfig($config_json);
        
        # Print the chart URL
        $url =  $qc->getUrl();
      }
      return $url;
    }

}
