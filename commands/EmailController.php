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
      $alertsConfig = \app\models\Alerts::find()->with('config')->asArray()->all();
      
      //loop alerts
      foreach($alertsConfig as $indexAlertsConfig => $alertConfig){
        // flag to send
        $flag = true;
        // get model the user
        $userModel = \app\models\Users::find()->select('email')->where(['id' => $alertConfig['userId'],'status' => 10])->one();

        if(!is_null($userModel)){
          // get info form alert
          $alertId = $alertConfig['id'];
          $alertName = $alertConfig['name'];
          $createdAt = \Yii::$app->formatter->asDatetime($alertConfig['createdAt'],'yyyy-MM-dd');
          $start_date = \Yii::$app->formatter->asDatetime($alertConfig['config']['start_date'],'yyyy-MM-dd');
          $end_date = \Yii::$app->formatter->asDatetime($alertConfig['config']['end_date'],'yyyy-MM-dd');

          if($alertConfig['status']){
            $status =  'Activa';
          }else{
            $status =  'Inactiva';
            // It is checked if the difference with the end date and the current day 
            //is greater than 24 hours to validate only one email send when the alert is inactive 
            $today = \app\helpers\DateHelper::getToday();
            $diffHours =  \app\helpers\DateHelper::diffInHours($end_date,$today);
            if($diffHours > 24){
              $flag = false;
            }
          }

          
          $count = (new \yii\db\Query())
          ->cache(10)
          ->from('alerts_mencions')
          ->join('JOIN', 'mentions', 'mentions.alert_mentionId = alerts_mencions.id')
          ->where(['alertId' => $alertId])
          ->count();
          
          if($count > 0 && $flag){
            $sourcesMentionsCount = \app\helpers\MentionsHelper::getCountSourcesMentions($alertId);
              
            if(isset($sourcesMentionsCount['data'])){
              // link graph
              $urlTotalResource = $this->getTotalResourceHyperLinkGraph($sourcesMentionsCount['data']);
              $urlIterationResource = $this->getIterationResourcesHyperLinkGraph($sourcesMentionsCount['data']);
              
              $productsMentionsCount = \app\helpers\MentionsHelper::getProductInteration($alertId);
              $urlIterationsProducts = $this->getIterarionByProductsLinkGraph($productsMentionsCount['data']);
              $urlCommonWords = $this->getCommonWordsByAlertIdLinkGraph($alertId);
              $urlEmojis = $this->getEmojisLinkGraph($alertId);
              $properties = [
                'width'=> 250,
                'height'=> 300,
                'leyend' => [
                    "display" => true,
                    "labels" => [
                        "fontSize" => 5
                      ]
                  ]
              ];
              $urlMentionsDateCount = \app\helpers\DocumentHelper::GraphResourceOnDate($alertConfig['id'],$properties);

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
                'hiperLinkMentionsDateCount' => $urlMentionsDateCount,
                'hiperCommonWords' => $urlCommonWords,
                'hiperLinkEmojis' => $urlEmojis,
                'frontendUrl' => \Yii::$app->params['frontendUrl'],
              ])
              ->setFrom('monitormtg@gmail.com')
              //->setTo($userModel->email)->setSubject("Alerta Monitor 📝: {$alertName}");
              ->setTo("spiderbbc@gmail.com")->setSubject("Alerta Monitor 📝: {$alertName}");
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
                'anchor' => 'center',
                'align' => 'center',
                'color' => '#fff',
                'font' => [
                  'weight'=> 'bold',
                ],
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
                'anchor' => 'center',
                'align' => 'center',
                'color' => '#fff',
                'font' => [
                  'weight'=> 'bold',
                ],
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

      // get top terms more total value
      usort($data, function($a, $b) {
        return end($b) - end($a);
      });
     
      $url = null;
      
      if(count($data)){
        if($data[0][0] != 'Not Found'){
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
                  'anchor' => 'center',
                  'align' => 'center',
                  'color' => '#fff',
                  'font' => [
                    'weight'=> 'bold',
                  ],
                  'formatter' => '(value) => { return value ;}'
                ]
              ]
            ]
          ];

          if(count($data) > 5){
              $data = array_slice($data, 0, 5); 
          }
         
          // top 5 products
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
      }
      return $url;
    }

    private function getCommonWordsByAlertIdLinkGraph($alertId){
     
      $words = \app\helpers\MentionsHelper::getCommonWordsByAlertId($alertId);
      $words = array_slice($words['words'],0,5);
        
      if(count($words)){
        $config = [
        'type' => 'outlabeledPie',
        'data' => [
            'labels' => [],
            'datasets' => [
                [
                    "backgroundColor" => ["#FF3784", "#36A2EB", "#4BC0C0", "#F77825", "#9966FF"],
                    "data" => []
                ]
            ],
        ],
        'options' => [
            'plugins' => [
                "legend" => false,
                "outlabels" => [
                        "text" => "%l %p",
                        "color" => "white",
                        "stretch" => 35,
                        "font" => [
                            'size' => 6
                            //"resizable" => true,
                            //"minSize" => 12,
                            //"maxSize" => 18
                        ]
                    ]    
                ]
            ]
        ];

        for($w = 0; $w < sizeOf($words); $w++){
            $config['data']['labels'][] = $words[$w]['name'];
            $config['data']['datasets'][0]['data'][] = $words[$w]['total'];
        }  

        $qc = new \QuickChart(array(
          'width'=> 250,
          'height'=> 300,
        ));


        $config_json = json_encode($config);
        $qc->setConfig($config_json);
        
        # Print the chart URL
        $url =  $qc->getShortUrl();
        return $url;
      }

      
      return null;
    }

    private function getEmojisLinkGraph($alertId){
      
      $data = \app\helpers\MentionsHelper::getEmojisListPointHex($alertId); 
      $emojis = array_slice($data['data'],0,10);
      $url  =  null;
      if(count($emojis)){
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
          foreach($emojis as $emoji => $values){
              $config['data']['labels'][] = \IntlChar::chr($values['unicode']);
              $config['data']['datasets'][0]['data'][] = $values['count'];
          }
  
          $qc = new \QuickChart(array(
            'width'=> 250,
            'height'=> 300,
          ));
  
  
          $config_json = json_encode($config);
          $qc->setConfig($config_json);
          
          $url =  $qc->getShortUrl();
          
      }
      return $url;
    }

}
