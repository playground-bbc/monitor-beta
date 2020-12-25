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
        
        return \app\helpers\DetailHelper::CommonWords($alertId,$resourceId,$term,$socialId);
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
     * return Regions user on view detail
     * @param integer $id
     * @param integer $resourceId
     * @param string $term
     */
    public function actionGetRegionLiveChat($alertId,$resourceId,$term = '',$socialId = ''){

        $where = ['alertId' => $alertId,'resourcesId' => $resourceId];
        
        if($term != ""){ $where['term_searched'] = $term;}
        
        $alert_mentions = \app\models\AlertsMencions::find()->where($where)->all();

        $user_ids = [];
        if(!empty($alert_mentions)){
            for ($a=0; $a < sizeOf($alert_mentions) ; $a++) { 
                if($alert_mentions[$a]->mentionsCount){
                    if ($socialId != "") {
                        $origins = $alert_mentions[$a]->getMentions()->select('origin_id')->where(['social_id'=> $socialId])->asArray()->all();
                    } else {
                        $origins = $alert_mentions[$a]->getMentions()->select('origin_id')->asArray()->all();
                    }

                    if(count($origins)){
                        for ($o=0; $o < sizeOf($origins) ; $o++) { 
                            $user_ids[] = $origins[$o];
                        }
                    }
                }
            }
        }
        
        
        $origin_ids = array_unique(\yii\helpers\ArrayHelper::getColumn($user_ids, 'origin_id'));
        
        $status = '"client"';
        $expressionSelect = new Expression("JSON_UNQUOTE(`user_data`->'$.geo.region') AS region,COUNT(*) AS num_geo");
        $expressionWhere = new Expression("JSON_CONTAINS(user_data,'{$status}','$.type')");
        $query = (new \yii\db\Query())
            ->cache(5)
            ->select($expressionSelect)
            ->from('users_mentions')
            ->where($expressionWhere)
            ->andWhere(['id' => $origin_ids])
            ->groupBy('region')
            ->all();
        
        $regions_count = [];
        if(count($query)){
            $regions = \app\helpers\MentionsHelper::getRegionsOnHcKey();
            for ($q=0; $q < sizeOf($query) ; $q++) { 
                if(!is_null($query[$q]['region']) && isset($regions[$query[$q]['region']])){
                    $regions_count [] = [
                        $regions[$query[$q]['region']], (int)$query[$q]['num_geo']
                    ];
                }
                
            }
        }


        return [
            'regions_count' => $regions_count,
            'query' => $query,
            'origin_ids' => $origin_ids,
        ];
    }

    /**
     * return city user on view detail
     * @param integer $id
     * @param integer $resourceId
     * @param array $options
     */
    public function actionGetCityLiveChat($alertId,$resourceId,$options,$socialId = ''){
        $options = json_decode($options,true);
        $where = ['alertId' => $alertId,'resourcesId' => $resourceId];
        
        $alert_mentions = \app\models\AlertsMencions::find()->where($where)->all();

        $user_ids = [];
        if(!empty($alert_mentions)){
            for ($a=0; $a < sizeOf($alert_mentions) ; $a++) { 
                if($alert_mentions[$a]->mentionsCount){
                    if ($socialId != '') {
                        $origins = $alert_mentions[$a]->getMentions()->select('origin_id')->where(['social_id' => $socialId])->asArray()->all();
                    } else {
                        $origins = $alert_mentions[$a]->getMentions()->select('origin_id')->asArray()->all();
                    }
                    
                    if(count($origins)){
                        for ($o=0; $o < sizeOf($origins) ; $o++) { 
                            $user_ids[] = $origins[$o];
                        }
                    }
                }
            }
        }
        $origin_ids = array_unique(\yii\helpers\ArrayHelper::getColumn($user_ids, 'origin_id'));

        $status = '"'.$options['hc-key'].'"';  
        
        $expressionSelect = new Expression("JSON_UNQUOTE(`user_data`->'$.geo.city') AS city,COUNT(*) AS num_city");
        $expressionWhere = new Expression("JSON_CONTAINS(user_data,'{$status}','$.geo.code')");
        
        $query = (new \yii\db\Query())
            ->cache(5)
            ->select($expressionSelect)
            ->from('users_mentions')
            ->where($expressionWhere)
            ->andWhere(['id' => $origin_ids])
            ->groupBy('city')
            ->all();

        return $query;
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