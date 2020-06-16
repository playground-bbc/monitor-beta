<?php

namespace app\models\grid;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Alerts;
use app\models\AlertConfig;
use app\models\Resources;

/**
 * AlertSearch represents the model behind the search form of `app\models\Alerts`.
 */
class AlertSearch extends Alerts
{
    public $start_date;
    public $end_date;
    public $alertResourceId;
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            //[['id', 'userId', 'status', 'createdAt', 'updatedAt', 'createdBy', 'updatedBy'], 'integer'],
            [['id', 'status'], 'integer'],
        //    [['alertResourceId'], 'string'],
            [['name','start_date','end_date','alertResourceId'], 'safe'],
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
    public function search($params)
    {   
        $userId = \Yii::$app->user->getId();
        $query = Alerts::find()->where(['userId' => $userId]);

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => ['pageSize' => 20],
        ]);

        $query->joinWith('config');
        $query->joinWith('config.sources');
        

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'status' => $this->status
        ]);
        
        // by name exactly
        $query->andFilterWhere(['like', Alerts::tableName() . '.name', $this->name]);
        // search by start-date and end-date
        if(($this->start_date != '') && ($this->end_date != ''))
            $query->andFilterWhere(['between', 'start_date', strtotime($this->start_date),strtotime($this->end_date)]);

        /*// date start_date
        if($this->start_date != '')
            $query->andFilterWhere(['like', AlertConfig::tableName() . '.start_date', strtotime($this->start_date)]);

        // date end_date
        if($this->end_date != '')
            $query->andFilterWhere(['like', AlertConfig::tableName() . '.end_date', strtotime($this->end_date)]);*/


        /*if($this->alertResourceId != '')
            $query->andFilterWhere(['like', Resources::tableName() .'.name', $this->alertResourceId]);*/
        
        $query->andFilterWhere(['like', Resources::tableName() .'.name', $this->alertResourceId]);    

        return $dataProvider;
    }
}
