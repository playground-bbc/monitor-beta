<?php

namespace app\modules\monitor\controllers\api;

use yii\rest\Controller;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use yii\web\Response;
use yii\web\NotFoundHttpException;

class MentionsController extends Controller
{

  public function behaviors(){
    return [
        [
            'class' => 'yii\filters\ContentNegotiator',
            'only' => [
              'status-alert',
              'count-mentions',
              'properties-source-box',
              'box-sources-count',
              'count-sources-mentions',
              'top-post-interation',
              'product-interation',
              'mention-on-date',
              'common-words',
              'web-page-domains',
              'list-mentions',
              'list-words',
              'list-emojis'
            ],  // in a controller
            // if in a module, use the following IDs for user actions
            // 'only' => ['user/view', 'user/index']
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
   * [actionCountMentions return count the total mentions / call component vue: total-mentions]
   * @param  [type] $alertId [description]
   * @return [type]          [description]
   */
  public function actionCountMentions($alertId){
   
    $model = $this->findModel($alertId);
    $data = [];

    if($model){
      $count = (new \yii\db\Query())
      ->cache(10)
      ->from('alerts_mencions')
      ->join('JOIN', 'mentions', 'mentions.alert_mentionId = alerts_mencions.id')
      ->where(['alertId' => $alertId])
      ->count();
      
      // total register
      $data['count'] = (int)$count;
    }
    
    return [
      'data' => $data,
    ];
  }

  public function actionPropertiesSourceBox($alertId){

    return \app\helpers\MentionsHelper::getPropertiesSourceBox($alertId);
     
  }
  /**
   * [actionBoxSourcesCount description]
   * @param  [type] $alertId [description]
   * @return [type]          [description]
   */
  public function actionBoxSourcesCount($alertId)
  {
   
    $model = $this->findModel($alertId);
    $modelDataCount = [];
    
    foreach ($model->config->sources as $sources){
      if(!\app\helpers\StringHelper::in_array_r($sources->name,$modelDataCount)){
          $modelDataCount[] = \app\helpers\AlertMentionsHelper::getSocialNetworkInteractions($sources->name,$sources->id,$model->id);
      }
    }
    $data = [];

    for($d = 0; $d < sizeof($modelDataCount); $d++){
      if(!is_null($modelDataCount[$d])){
        $name = $modelDataCount[$d][0];
        $total = $modelDataCount[$d][4];
        
        $data[] = array($name,$total);
      }
    }

    return array('status' => true,'data' => $data,'modelDataCount' => $modelDataCount);
  }

  
  /**
   * [actionCountSourcesMentions count by sources / call component vue: total-resources-chart]
   * @param  [type] $alertId [description]
   * @return [type]          [description]
   */
  public function actionCountSourcesMentions($alertId){
    
    return \app\helpers\MentionsHelper::getCountSourcesMentions($alertId);

  }
  /**
   * [actionTopPostInteration top post face or instagram with more interation / call component vue: post-interation-chart]
   * @param  [type] $alertId [description]
   * @return [type]          [description]
   */
  public function actionTopPostInteration($alertId)
  {
   
    $status = true;
    $model = \app\models\Alerts::findOne($alertId);
    $data = [];
    foreach ($model->config->sources as $sources){
      $data[] = \app\helpers\AlertMentionsHelper::getPostInteractions($sources->name,$sources->id,$model->id);
    }

    // reorder array

    $model = [];
    for ($d=0; $d <sizeof($data) ; $d++) { 
      if(!is_null($data[$d])){
        for ($s=0; $s <sizeof($data[$d]) ; $s++) { 
          if(is_numeric($data[$d][$s])){
            $data[$d][$s] = intval($data[$d][$s]);
          }
          $model[] = $data[$d][$s];
        }
      }
    }

    if(empty($model)){
      $model[] = ['not found',0,0,0,0];
      $status = false;
    }
    return array('status'=>$status,'data'=>$model);

  }

  /**
   * [actionProductInteration interations by products / call component vue: products-interations-chart]
   * @param  [type] $alertId [description]
   * @return [type]          [description]
   */
  public function actionProductInteration($alertId)
  {
    return \app\helpers\MentionsHelper::getProductInteration($alertId);
  }


  public function actionResourceOnDateChart($alertId)
  {
   
    $model = \app\models\Alerts::findOne($alertId);
    $alerts_mentions = \app\models\AlertsMencions::find()->where(['alertId' => $model->id])->all();

    // get resources with mentions
    $resources = [];
    foreach ($alerts_mentions as $alerts_mention) {
      if($alerts_mention->mentionsCount){
        $resources[] = $alerts_mention->resources->name;
      }// end if
    }// end foreach

    // get data by date for each resources
    $data = [];

    return array('status'=>true,'resources'=> $resources,'data' => $data);
  }


  /**
   * [actionListMentions list all mention by id / call component vue: list-mentions]
   * @param  [type] $alertId [description]
   * @return [type]          [description]
   */
  public function actionListMentions($alertId){
    // list mentions: resource - products - author - mentions
    $db = \Yii::$app->db;
    $duration = 60;  
    $alertMentions = $db->cache(function ($db) use ($alertId) {
      return (new \yii\db\Query())
        ->select('id')
        ->from('alerts_mencions')
        ->where(['alertId' => $alertId])
        ->orderBy(['resourcesId' => 'ASC'])
        ->all();
    },$duration); 
    
    $alertsId = \yii\helpers\ArrayHelper::getColumn($alertMentions,'id');  

    //$mentions = \app\models\Mentions::find()->where(['alert_mentionId' => $alertsId])->with(['alertMention','alertMention.resources','origin'])->asArray()->all();
    
    $rows = $db->cache(function ($db) use ($alertsId) {
      return (new \yii\db\Query())
        ->select([
          'recurso' => 'r.name',
          'term_searched' => 'a.term_searched',
          'created_time' => 'm.created_time',
          'name' => 'u.name',
          'screen_name' => 'u.screen_name',
          'subject' => 'm.subject',
          'message_markup' => 'm.message_markup',
          'url' => 'm.url',
        ])
        ->from('mentions m')
        ->where(['alert_mentionId' => $alertsId])
        ->join('JOIN','alerts_mencions a', 'm.alert_mentionId = a.id')
        ->join('JOIN','resources r', 'r.id = a.resourcesId')
        ->join('JOIN','users_mentions u', 'u.id = m.origin_id')
        ->all();
    },$duration);


    return array('data' => $rows);

  }

  /**
   * [actionListWords list words found it / call component vue: cloud-words]
   * @param  [type] $alertId [description]
   * @return [type]          [description]
   */
  public function actionListWords($alertId){
  

    $alertsKeywords = \app\modules\wordlists\models\AlertsKeywords::find()->where(['alertId' => $alertId])->all();

    $wordsModel = [];
    $index = 0;
    
    foreach($alertsKeywords as $alertKeyword){
      if($alertKeyword->keyword->keywordsMentions){
          $wordsModel[$index]['text']      = $alertKeyword->keyword->name;
          $wordsModel[$index]['weight']    = (int)$alertKeyword->keyword->getKeywordsMentions()->count();
          $index++; 
      }
     
    }

    return array('status'=>true,'wordsModel' => $wordsModel);
  }

  /**
   * [actionResourceOnDate description / call component vue: resource-date-mentions]
   * @param  [type] $alertId [description]
   * @return [type]          [description]
   */
  public function actionResourceOnDate($alertId){
   
    //menciones por recurso y fecha
    $expression = new Expression("DATE(FROM_UNIXTIME(created_time)) AS date,COUNT(*) AS total");
    $expressionGroup = new Expression("DATE(FROM_UNIXTIME(created_time))");
    
    $alertMentions = \app\models\AlertsMencions::find()->where(['alertId' => $alertId])->orderBy(['resourcesId' => 'ASC'])->all();
    $resourceDateCount = [];
    
    foreach ($alertMentions as $alertMention){
      if($alertMention->mentionsCount){
        if(!in_array($alertMention->resources,$resourceDateCount)){
          $rows = (new \yii\db\Query())
          ->select($expression)
          ->from('mentions')
          ->where(['alert_mentionId' => $alertMention->id])
          ->orderBy('total DESC')
          ->groupBy($expressionGroup)
          ->all();


          foreach ($rows as $row){
            $row['product_searched'] = $alertMention->term_searched;
            $resourceDateCount[$alertMention->resources->name][] = $row;  
          }

          
        } // end if in_array

      }// is not null 
      
    }// end foreach
    return array('status'=>true,'resourceDateCount' => $resourceDateCount);  
  }


    /**
   * [actionListEmojis list emojis count in mentions / call component vue: list-emojis]
   * @param  [type] $alertId [description]
   * @return [type]          [description]
   */
  public function actionListEmojis($alertId){
    return \app\helpers\MentionsHelper::getEmojisList($alertId);   

  }

   /**
   * [actionStatusAlert return a list wiht social media and his status / call component vue: status-alert and modal-alert]
   * @param  [int] $alertId [id of the alert]
   * @return [json]          [list social media and his status]
   */
  public function actionStatusAlert($alertId)
  {
    $model =  \app\models\HistorySearch::findOne(['alertId' => $alertId]);
    return array('data' => $model);  
  }
  /**
   * [actionCountByProducts count mentions by products]
   * @param  [type] $alertId [description]
   * @return [type]          [description]
   */
  public function actionCountByProducts($alertId){

   
    // cuenta por resource and producto
    $alertMentions = \app\models\AlertsMencions::find()->where(['alertId' => $alertId])->orderBy(['resourcesId' => 'ASC'])->all();
    $resourceCount = [];
    foreach ($alertMentions as $alertMention){
      $mentionCount = \app\models\Mentions::find()->where(['alert_mentionId' => $alertMention->id])->count(); 
      if($mentionCount){
        $resourceCount[$alertMention->resources->name][$alertMention->term_searched][] = $mentionCount;
      }
    }
    return array('status'=>true,'resources'=>$resourceCount);


  }

  /**
   * [actionGetResourceId return id from resource]
   * @param  [type] $resourceName [description]
   * @return [type]               [description]
   */
  public function actionGetResourceId($resourceName){
    $model = \app\models\Resources::find()->where(['name' => $resourceName])->one(); 
    return array('status'=>true,'resourceId'=>$model->id);

  }


  /**
   * [actionMentionOnDate return array of date to graph]
   * @param  [int] $id            [id of alert]
   * @return [array \ Exception]  [array of date or exception if alert id not exists]
   */
  public function actionMentionOnDate($alertId){
    return  \app\helpers\MentionsHelper::getMentionOnDate($alertId);  
  }

   /**
   * [actionCommonWords return array of commons words
   * @param  [int] $id            [id of alert]
   * @return [array \ Exception]  [array of date or exception if alert id not exists]
   */
  public function actionCommonWords($alertId){
    return  \app\helpers\MentionsHelper::getCommonWordsByAlertId($alertId);
  }

  /**
   * [actionWebPageRetails return array web page retails
   * @param  [int] $id            [id of alert]
   * @return [array \ Exception]  [array of date or exception if alert id not exists]
   */
  public function actionWebPageDomains($alertId){
    return  \app\helpers\MentionsHelper::getDomainsFromMentionsOnUrls($alertId);
  }

  /**
   * Finds the Alerts model based on its primary key value.
   * If the model is not found, a 404 HTTP exception will be thrown.
   * @param integer $id
   * @return Alerts the loaded model
   * @throws NotFoundHttpException if the model cannot be found
   */
  protected function findModel($id)
  {
      if (($model = \app\models\Alerts::findOne($id)) !== null) {
          return $model;
      }

      throw new NotFoundHttpException('The requested page does not exist.');
  }

}
