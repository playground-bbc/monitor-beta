<?php

namespace app\models\grid;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Alerts;
use app\models\Mentions;
use app\models\AlertConfig;
use app\models\Resources;

/**
 * MentionSearch represents the model behind the search form of `app\models\Mentions`.
 */
class MentionSearch extends Mentions
{
    public $resourceName;
    public $termSearch;
    public $name;
    public $screen_name;
    public $subject;
    public $message_markup;
    // for search grid
    public $pageSize = 10;
    // detail
    public $resourceId;
    public $social_id;
    public $mention_data;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['resourceName','termSearch','name','screen_name','subject','message_markup','mention_data'], 'string'],
            [['resourceId','social_id'], 'integer'],
            [['resourceName'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params,$alertId)
    {   
       
        
        $model = $this->getData($params,$alertId);
        
        $dataProvider = new \yii\data\ArrayDataProvider([
            'allModels' => $model,
            'pagination' => [
                'pageSize' => $this->pageSize,
            ],
            'totalCount' => count($model)
        ]);


        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }


        return $dataProvider;
    }


    public function getData($params,$alertId){
        
        $limit = -1;
        $offset = -1;
        // set limit and offset
        if(\yii\helpers\ArrayHelper::keyExists('page',$params) && \yii\helpers\ArrayHelper::keyExists('per-page',$params)){
            // limit = pageSize * page && offset = limit -
            $limit = (int) $this->pageSize * $params['page'];
            $$offset = (int) $params['page'] / $this->pageSize;
        }else{
            $limit = $this->pageSize;
        }
        $db = \Yii::$app->db;
        $duration = 60;
        
        $where['alertId'] = $alertId;
        if(isset($params['resourceId'])){
            $where['resourcesId'] = $params['resourceId'];
        }
        
    
        $alertMentions = $db->cache(function ($db) use ($where) {
          return (new \yii\db\Query())
            ->select('id')
            ->from('alerts_mencions')
            ->where($where)
            ->orderBy(['resourcesId' => 'ASC'])
            ->all();
        },$duration); 
        
        $alertsId = \yii\helpers\ArrayHelper::getColumn($alertMentions,'id');  
        
        $rows = (new \yii\db\Query())
        ->select([
          'recurso' => 'r.name',
          'term_searched' => 'a.term_searched',
          'created_time' => 'm.created_time',
          'name' => 'u.name',
          'screen_name' => 'u.screen_name',
          'subject' => 'm.subject',
          'message_markup' => 'm.message_markup',
          'social_id' => 'm.social_id',
          'mention_data' => 'm.mention_data',
          'url' => 'm.url',
        ])
        ->from('mentions m')
        ->where(['alert_mentionId' => $alertsId])
        ->join('JOIN','alerts_mencions a', 'm.alert_mentionId = a.id')
        ->join('JOIN','resources r', 'r.id = a.resourcesId')
        ->join('JOIN','users_mentions u', 'u.id = m.origin_id')
        ->orderBy(['m.created_time' => 'ASC'])
        ->all();

        // var_dump($rows);
        // die();

        if ($this->load($params)) {

            if($this->social_id != ''){
                $name = strtolower(trim($this->social_id));
                $rows = array_filter($rows, function ($role) use ($name) {
                    return (empty($name) || strpos((strtolower(is_object($role) ? $role->recurso : $role['social_id'])), $name) !== false);
                });
            }

            if($this->resourceName != ''){
                $name = strtolower(trim($this->resourceName));
                $rows = array_filter($rows, function ($role) use ($name) {
                    return (empty($name) || strpos((strtolower(is_object($role) ? $role->recurso : $role['recurso'])), $name) !== false);
                });
            }

            if($this->termSearch != ''){
                $name = strtolower(trim($this->termSearch));
                $rows = array_filter($rows, function ($role) use ($name) {
                    return (empty($name) || strpos((strtolower(is_object($role) ? $role->term_searched : $role['term_searched'])), $name) !== false);
                });
            }

            if($this->name != ''){
                $name = strtolower(trim($this->name));
                $rows = array_filter($rows, function ($role) use ($name) {
                    return (empty($name) || strpos((strtolower(is_object($role) ? $role->name : $role['name'])), $name) !== false);
                });
            }

            if($this->screen_name != ''){
                $name = strtolower(trim($this->screen_name));
                $rows = array_filter($rows, function ($role) use ($name) {
                    return (empty($name) || strpos((strtolower(is_object($role) ? $role->screen_name : $role['screen_name'])), $name) !== false);
                });
            }

            if($this->subject != ''){
                $name = strtolower(trim($this->subject));
                $rows = array_filter($rows, function ($role) use ($name) {
                    return (empty($name) || strpos((strtolower(is_object($role) ? $role->subject : $role['subject'])), $name) !== false);
                });
            }

            if($this->message_markup != ''){
                $name = strtolower(trim($this->message_markup));
                $rows = array_filter($rows, function ($role) use ($name) {
                    return (empty($name) || strpos((strtolower(is_object($role) ? $role->message_markup : $role['message_markup'])), $name) !== false);
                });
            }

            if($this->mention_data != ''){
                $name = strtolower(trim($this->mention_data));
                $rows = array_filter($rows, function ($role) use ($name) {
                    $role = json_decode($role['mention_data'],true);
                    return (empty($name) || strpos((strtolower(is_object($role) ? $role->message_markup : $role['status'])), $name) !== false);
                });
            }
            
        }

        return $rows;
    }
    
    public function getTotalCount($alertId,$params = null){
       
        $db = \Yii::$app->db;
        $duration = 60; 
        
        $where['alertId'] = $alertId;
        if(isset($params['resourceId'])){
            $where['resourcesId'] = $params['resourceId'];
        }
        
        if(isset($params['MentionSearch']['termSearch'])){
            $where['term_searched'] = $params['MentionSearch']['termSearch'];
        }
        

        $alertMentions = $db->cache(function ($db) use ($where) {
            return (new \yii\db\Query())
              ->select('id')
              ->from('alerts_mencions')
              ->where($where)
              ->orderBy(['resourcesId' => 'ASC'])
              ->all();
          },$duration); 
          
          $alertsId = \yii\helpers\ArrayHelper::getColumn($alertMentions,'id');  
         
          
          $totalCount = (new \yii\db\Query())
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
          ->count();
          
        return (int)$totalCount;  

    }
}
