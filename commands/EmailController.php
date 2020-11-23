<?php

namespace app\commands;

use yii\console\Controller;
use yii\console\ExitCode;
use app\models\Dictionaries;
use yii\helpers\Console;
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
            'alias' => 'Live Chat T', 
            'background' => '5cfdf0',
        ],
        'Live Chat Conversations' => [
            'alias' => 'Live Chat C', 
            'background' => '5cc2fd',
        ],
        'Facebook Comments' => [
            'alias' => 'Facebook C', 
            'background' => '945cfd',
        ],
        'Facebook Messages' => [
            'alias' => 'Facebook M', 
            'background' => 'd75cfd',
        ],
        'Instagram Comments' => [
            'alias' => 'Instagram C', 
            'background' => 'fd5cfd',
        ],
        'Paginas Webs' => [
            'alias' => 'Paginas W', 
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
            \Yii::$app->mailer->compose('insights',['model' => $model,'pathLogo' => $pathLogo])
            ->setFrom('monitormtg@gmail.com')
            ->setTo(["spiderbbc@gmail.com"])->setSubject("Insigths de la Cuenta 📝: Mundo Lg")->send();
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
                // link total resources graph
                $hiperLinkTotalResource = $this->getTotalResourceHyperLinkGraph($sourcesMentionsCount['data']);
                //$hiperLinkIterationResource = $this->getIterationResourcesHyperLinkGraph($sourcesMentionsCount['data']);
                $productsMentionsCount = \app\helpers\MentionsHelper::getProductInteration($alertId);
                $hiperLinkIterationByProducts = $this->getIterarionByProductsLinkGraph($productsMentionsCount['data']);
                if(!is_null($hiperLinkIterationByProducts)){
                    \Yii::$app->mailer->compose('alerts',[
                        'alertName' => $alertName,
                        'createdAt' => $createdAt,
                        'start_date' => $start_date,
                        'end_date' => $end_date,
                        'status' => $status,
                        'hiperLinkTotalResource' => $hiperLinkTotalResource,
                        'hiperLinkIterationResource' => null,
                        'hiperLinkIterationByProducts' => $hiperLinkIterationByProducts,
                    ])
                    ->setFrom('monitormtg@gmail.com')
                    ->setTo([$userModel->email])->setSubject("Alerta Monitor 📝: Mundo Lg")->send();
                }

            }
            
        }
            
                

        return ExitCode::OK;
    }

    private function getTotalResourceHyperLinkGraph($sourcesMentionsCount){
        
        $countData = count($sourcesMentionsCount) -1;
        $chdl = '';
        $chd = 't:';
        $chm = '';
        $chco = '';
        // loop over sources count
        foreach($sourcesMentionsCount as $indexSourcesMentions => $sourceMentionsCount){
            // put resources
            $resourceName = $this->resourceProperties[$sourceMentionsCount[0]];
            $chdl .= "{$resourceName['alias']}";
            // put total
            $total = $sourceMentionsCount[3];
            $chd .= "{$total}";
            // put chm
            $chm .= "N,000000,{$indexSourcesMentions},,10";
            // put chco
            $chco .= "{$resourceName['background']}";
            if($countData != $indexSourcesMentions){
                $chdl .= "|";
                $chd .= "|";
                $chm .= "|";
                $chco .= ",";
            }
            
        }
        $chd = urlencode($chd);
        $chdl = urlencode($chdl);
        $chm = urlencode($chm);
        $chco = urlencode($chco);
        
        $hiperlink = "https://image-charts.com/chart?chbh=a&chbr=10&chco={$chco}&chd={$chd}&chdl={$chdl}&chm={$chm}&chma=0%2C0%2C10%2C10&chs=550x150&chds=0%2C100000&cht=bvg&chxs=0%2C000000%2C0%2C0%2C_&chxt=y";
        return $hiperlink;
    }

    private function getIterationResourcesHyperLinkGraph($sourcesMentionsCount){
        
        $countData = count($sourcesMentionsCount) -1;
        $chd = 't:';
        $chm = '';
        $chxl = '0:|';
        $data = [];
        // loop over sources count
        
    }

    private function getIterarionByProductsLinkGraph($productsMentionsCount){
        
        if(count($productsMentionsCount)){
            $chli = $chtt = $productsMentionsCount[0][0];
            $chdl = 'Shares/Retweets|Likes|Total';
            $chl = '';
            $chd = 't:';
            $chco = '5cfdf0,fd5c5c,945cfd';

            for ($i = 1; $i < count($productsMentionsCount[0]); $i++) {
               // asignado el mismo valor a distintas variables
               $chd  .=  $productsMentionsCount[0][$i];
               $chl  .= $productsMentionsCount[0][$i];
               if($i < 3){
                $chd.= ",";
                $chl.= "|";
               }
                
            }
            $chli = urlencode($chli);
            $chdl = urlencode($chdl);
            $chl = urlencode($chl);
            $chd = urlencode($chd);
            $chco = urlencode($chco);
            
            $hiperlink = "https://image-charts.com/chart?chan=1200&chco={$chco}&chd={$chd}&chdl={$chdl}&chdlp=b&chl={$chl}&chli={$chli}&chma=0%2C0%2C0%2C10&chs=550x150&cht=pd&chtt={$chli}";
            return $hiperlink;
        }
        return null;
    }

}
