<?php

namespace app\commands;

use yii\console\Controller;
use yii\console\ExitCode;
use app\models\Dictionaries;
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
}
