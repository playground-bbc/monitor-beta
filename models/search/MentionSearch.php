<?php

namespace app\models\search;

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

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['resourceName','termSearch','name','screen_name','subject','message_markup'], 'string'],
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
                'pageSize' => 10,
            ]
        ]);


        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }


        return $dataProvider;
    }


    public function getData($params,$alertId){
        
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
        
        $rows = (new \yii\db\Query())
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

        if ($this->load($params)) {

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
            
        }


        return $rows;
    }
}
