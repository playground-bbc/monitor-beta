<?php

namespace app\modules\topic\models;

use Yii;

/**
 * This is the model class for table "m_topics_location".
 *
 * @property int $id
 * @property int $topicId
 * @property int $locationId
 * @property int|null $createdAt
 * @property int|null $updatedAt
 * @property int|null $createdBy
 * @property int|null $updatedBy
 *
 * @property MLocations $location
 * @property MTopics $topic
 */
class MTopicsLocation extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'm_topics_location';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['topicId', 'locationId'], 'required'],
            [['topicId', 'locationId', 'createdAt', 'updatedAt', 'createdBy', 'updatedBy'], 'integer'],
            [['locationId'], 'exist', 'skipOnError' => true, 'targetClass' => MLocations::className(), 'targetAttribute' => ['locationId' => 'id']],
            [['topicId'], 'exist', 'skipOnError' => true, 'targetClass' => MTopics::className(), 'targetAttribute' => ['topicId' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'topicId' => Yii::t('app', 'Topic ID'),
            'locationId' => Yii::t('app', 'Location ID'),
            'createdAt' => Yii::t('app', 'Created At'),
            'updatedAt' => Yii::t('app', 'Updated At'),
            'createdBy' => Yii::t('app', 'Created By'),
            'updatedBy' => Yii::t('app', 'Updated By'),
        ];
    }

    /**
     * Gets query for [[Location]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getLocation()
    {
        return $this->hasOne(MLocations::className(), ['id' => 'locationId']);
    }

    /**
     * Gets query for [[Topic]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTopic()
    {
        return $this->hasOne(MTopics::className(), ['id' => 'topicId']);
    }
}
