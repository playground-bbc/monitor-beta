<?php

namespace app\modules\monitor\controllers\api;

use yii\rest\Controller;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use yii\web\Response;
use yii\web\NotFoundHttpException;


/**
 * class controller to Api widget
 */
class DetailController extends Controller {


    /**
	 * [behaviors negotiator to return the response in json format]
	 * @return [array] [for controller]
	 */
	public function behaviors(){
        return [
             [
                 'class' => 'yii\filters\ContentNegotiator',
                 'only' => [
                 ],  // in a controller
                 // if in a module, use the following IDs for user actions
                 'formats' => [
                     'application/json' => Response::FORMAT_JSON,
                 ],
                 'languages' => [
                     'en',
                     'de',
                 ],
             ],
        ];
     }
    /**
     * get the count records by alert and resourceId.
     * @param integer $id
     * @param integer $resourceId
     * @param string $term
     * @return $count the total record
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionCount($alertId,$resourceId,$term = ""){
        
        $model = $this->findModel($alertId,$resourceId);

        $db = \Yii::$app->db;
        $duration = 60; 
        $where = ['alertId' => $alertId,'resourcesId' => $resourceId];
        if($term != ""){
            $where['term_searched'] = $term;
        }

        $countMentions = $db->cache(function ($db) use ($alertId,$resourceId,$where) {
            return (new \yii\db\Query())
            ->from('alerts_mencions')
            ->join('JOIN', 'mentions', 'mentions.alert_mentionId = alerts_mencions.id')
            ->where($where)
            ->count();
        },$duration);

        return ['countMentions' => (int) $countMentions];
    }

    /**
     * return property to compose on view box.info
     * @param integer $id
     * @param integer $resourceId
     * @param string $term
     * @return $count the total record
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionBoxInfo($alertId,$resourceId,$term = '',$socialId = ''){
        
        $model = $this->findModel($alertId,$resourceId);
        $resourceName = \app\helpers\AlertMentionsHelper::getResourceNameById($resourceId);

        $propertyBoxs = [];

        if($resourceName == "Twitter"){
            $propertyBoxs = \app\helpers\DetailHelper::setBoxPropertiesTwitter($model->id,$resourceId,$term);
        }

        if($resourceName == "Live Chat"){
            $propertyBoxs = \app\helpers\DetailHelper::setBoxPropertiesLiveChat($model->id,$resourceId,$term,$socialId);
        }

        if($resourceName == "Live Chat Conversations"){
            $propertyBoxs = \app\helpers\DetailHelper::setBoxPropertiesLiveChatConversation($model->id,$resourceId,$term,$socialId);
        }

        if($resourceName == "Facebook Comments"){
            $propertyBoxs = \app\helpers\DetailHelper::setBoxPropertiesFaceBookComments($model->id,$resourceId,$term,$socialId);
        }
        if($resourceName == "Facebook Messages"){
            $propertyBoxs = \app\helpers\DetailHelper::setBoxPropertiesFaceBookMessages($model->id,$resourceId,$term,$socialId);
        }

        if($resourceName == "Instagram Comments"){
            $propertyBoxs = \app\helpers\DetailHelper::setBoxPropertiesInstagramComments($model->id,$resourceId,$term,$socialId);
        }

        if($resourceName == "Paginas Webs"){
            $propertyBoxs = \app\helpers\DetailHelper::setBoxPropertiesPaginasWebs($model->id,$resourceId,$term);
        }
        return ['propertyBoxs' => $propertyBoxs];
    }
    /**
     * return common words and his weight on view common-words-detail
     * @param integer $id
     * @param integer $resourceId
     * @param string $term
     * @return $data total words common
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionCommonWords($alertId,$resourceId,$term = '',$socialId = ''){
        $model = $this->findModel($alertId,$resourceId);
        $where = ['alertId' => $alertId,'resourcesId' => $resourceId];
        
        if($term != ""){ $where['term_searched'] = $term;}
        
        $where_alertMentions = [];
        if($socialId != ""){ $where_alertMentions['mention_socialId'] = $socialId;}

        $alertsMentionsIds = \app\models\AlertsMencions::find()->select('id')->where($where)->asArray()->all();

        // SELECT name,SUM(weight) as total FROM `alerts_mencions_words` WHERE  alert_mentionId IN (166,171,175,177,181,170,172,182) AND weight > 2 GROUP BY name  
        // ORDER BY `total`  DESC
        $ids = \yii\helpers\ArrayHelper::getColumn($alertsMentionsIds, 'id');
        $where_alertMentions['alert_mentionId'] = $ids;
        
        $rows = (new \yii\db\Query())
        ->select(['name','total' => 'SUM(weight)'])
        ->from('alerts_mencions_words')
        ->where($where_alertMentions)
        ->groupBy('name')
        ->orderBy(['total' => SORT_DESC])
        ->limit(20)
        ->all();
        
        $data = [];
        for ($r=0; $r < sizeOf($rows) ; $r++) { 
            if($rows[$r]['total'] >= 2){
                $data[]= $rows[$r];
            }
        }

        return ['words' => $data,'alertsMentionsIds' => $ids,'not-filter'=> $rows];
    }
    /**
     * return post or ticket to second select2 on view detail
     * @param integer $id
     * @param integer $resourceId
     * @param string $term
     */
    public function actionSelectDepend($alertId,$resourceId,$term = ''){
        
        $model = $this->findModel($alertId,$resourceId);
        $resourceName = \app\helpers\AlertMentionsHelper::getResourceNameById($resourceId);

        $data = [['id' => '', 'text' => '']];
        if($resourceName == "Live Chat"){
            $data =  \app\helpers\DetailHelper::getTicketLiveChat($model->id,$resourceId,$term);
        }
        if($resourceName == "Live Chat Conversations"){
            $data =  \app\helpers\DetailHelper::getChatsLiveChat($model->id,$resourceId,$term);
        }
        if($resourceName == "Facebook Comments" || $resourceName == "Instagram Comments"){
            $data =  \app\helpers\DetailHelper::getPostsFaceBookComments($model->id,$resourceId,$term);
        }

        if($resourceName == "Facebook Messages"){
            $data =  \app\helpers\DetailHelper::getInboxFaceBookComments($model->id,$resourceId,$term);
        }
        
        return ['data' => $data];
    }
    /**
     * Finds the Alerts model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Alerts the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id,$resourceId)
    {
        if (($model = \app\models\Alerts::findOne($id)) !== null) {
            $alertResources = \yii\helpers\ArrayHelper::map($model->config->sources,'id','name');
            if(in_array($resourceId,array_keys($alertResources))){
                return $model;
            }else{
                throw new NotFoundHttpException('The resource page does not exist for this Alert.');  
            }
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

}