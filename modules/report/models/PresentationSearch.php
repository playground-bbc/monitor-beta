<?php

namespace app\modules\report\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\modules\report\models\Presentation;

/**
 * PresentationSearch represents the model behind the search form of `app\modules\report\models\Presentation`.
 */
class PresentationSearch extends Presentation
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'userId', 'date', 'status', 'updated', 'createdAt', 'updatedAt', 'createdBy', 'updatedBy'], 'integer'],
            [['name', 'head_title', 'title', 'url_sheet', 'url_presentation'], 'safe'],
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
        $query = Presentation::find()->where(['userId' => $userId]);

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'userId' => $this->userId,
            'date' => $this->date,
            'status' => $this->status,
            'updated' => $this->updated,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
            'createdBy' => $this->createdBy,
            'updatedBy' => $this->updatedBy,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'head_title', $this->head_title])
            ->andFilterWhere(['like', 'title', $this->title])
            ->andFilterWhere(['like', 'url_sheet', $this->url_sheet])
            ->andFilterWhere(['like', 'url_presentation', $this->url_presentation]);

        return $dataProvider;
    }
}
