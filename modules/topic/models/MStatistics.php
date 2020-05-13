<?php

namespace app\modules\topic\models;

use Yii;

/**
 * This is the model class for table "m_statistics".
 *
 * @property int $id
 * @property int $topicStaticId
 * @property int $total
 * @property int $timespan
 * @property int|null $createdAt
 * @property int|null $updatedAt
 * @property int|null $createdBy
 * @property int|null $updatedBy
 *
 * @property MAttachments[] $mAttachments
 * @property MTopicsStadistics $topicStatic
 * @property MWordsDictionaryStatistic[] $mWordsDictionaryStatistics
 */
class MStatistics extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'm_statistics';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['topicStaticId', 'total', 'timespan'], 'required'],
            [['topicStaticId', 'total', 'timespan', 'createdAt', 'updatedAt', 'createdBy', 'updatedBy'], 'integer'],
            [['topicStaticId'], 'exist', 'skipOnError' => true, 'targetClass' => MTopicsStadistics::className(), 'targetAttribute' => ['topicStaticId' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'topicStaticId' => Yii::t('app', 'Topic Static ID'),
            'total' => Yii::t('app', 'Total'),
            'timespan' => Yii::t('app', 'Timespan'),
            'createdAt' => Yii::t('app', 'Created At'),
            'updatedAt' => Yii::t('app', 'Updated At'),
            'createdBy' => Yii::t('app', 'Created By'),
            'updatedBy' => Yii::t('app', 'Updated By'),
        ];
    }

    /**
     * Gets query for [[MAttachments]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getMAttachments()
    {
        return $this->hasMany(MAttachments::className(), ['statisticId' => 'id']);
    }

    /**
     * Gets query for [[TopicStatic]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTopicStatic()
    {
        return $this->hasOne(MTopicsStadistics::className(), ['id' => 'topicStaticId']);
    }

    /**
     * Gets query for [[MWordsDictionaryStatistics]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getMWordsDictionaryStatistics()
    {
        return $this->hasMany(MWordsDictionaryStatistic::className(), ['statisticId' => 'id']);
    }
}
