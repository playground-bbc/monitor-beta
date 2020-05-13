<?php

namespace app\modules\topic\models;

use Yii;

/**
 * This is the model class for table "m_words_dictionary_statistic".
 *
 * @property int $id
 * @property int $keywordId
 * @property int $statisticId
 * @property int $count
 * @property int|null $createdAt
 * @property int|null $updatedAt
 * @property int|null $createdBy
 * @property int|null $updatedBy
 *
 * @property MKeywords $keyword
 * @property MStatistics $statistic
 */
class MWordsDictionaryStatistic extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'm_words_dictionary_statistic';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['keywordId', 'statisticId', 'count'], 'required'],
            [['keywordId', 'statisticId', 'count', 'createdAt', 'updatedAt', 'createdBy', 'updatedBy'], 'integer'],
            [['keywordId'], 'exist', 'skipOnError' => true, 'targetClass' => MKeywords::className(), 'targetAttribute' => ['keywordId' => 'id']],
            [['statisticId'], 'exist', 'skipOnError' => true, 'targetClass' => MStatistics::className(), 'targetAttribute' => ['statisticId' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'keywordId' => Yii::t('app', 'Keyword ID'),
            'statisticId' => Yii::t('app', 'Statistic ID'),
            'count' => Yii::t('app', 'Count'),
            'createdAt' => Yii::t('app', 'Created At'),
            'updatedAt' => Yii::t('app', 'Updated At'),
            'createdBy' => Yii::t('app', 'Created By'),
            'updatedBy' => Yii::t('app', 'Updated By'),
        ];
    }

    /**
     * Gets query for [[Keyword]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getKeyword()
    {
        return $this->hasOne(MKeywords::className(), ['id' => 'keywordId']);
    }

    /**
     * Gets query for [[Statistic]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getStatistic()
    {
        return $this->hasOne(MStatistics::className(), ['id' => 'statisticId']);
    }
}
