<?php

namespace app\modules\wordlists\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Keywords;

/**
 * KeywordsSearch represents the model behind the search form of `app\models\Keywords`.
 */
class KeywordsSearch extends Keywords
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'dictionaryId', 'createdAt', 'updatedAt', 'createdBy', 'updatedBy'], 'integer'],
            [['name'], 'safe'],
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
        $query = Keywords::find();

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
            'dictionaryId' => $this->dictionaryId,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
            'createdBy' => $this->createdBy,
            'updatedBy' => $this->updatedBy,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name]);

        return $dataProvider;
    }
}
