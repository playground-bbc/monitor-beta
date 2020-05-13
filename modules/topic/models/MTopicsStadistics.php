<?php

namespace app\modules\topic\models;

use Yii;

/**
 * This is the model class for table "m_topics_stadistics".
 *
 * @property int $id
 * @property int|null $topicId
 * @property int $resourceId
 * @property int|null $locationId
 * @property int $wordId
 * @property int|null $status
 * @property int|null $createdAt
 * @property int|null $updatedAt
 * @property int|null $createdBy
 * @property int|null $updatedBy
 *
 * @property MAttachments[] $mAttachments
 * @property MStatistics[] $mStatistics
 * @property MResources $resource
 * @property MTopics $topic
 * @property MWords $word
 */
class MTopicsStadistics extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'm_topics_stadistics';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['topicId', 'resourceId', 'locationId', 'wordId', 'status', 'createdAt', 'updatedAt', 'createdBy', 'updatedBy'], 'integer'],
            [['resourceId', 'wordId'], 'required'],
            [['resourceId'], 'exist', 'skipOnError' => true, 'targetClass' => MResources::className(), 'targetAttribute' => ['resourceId' => 'id']],
            [['topicId'], 'exist', 'skipOnError' => true, 'targetClass' => MTopics::className(), 'targetAttribute' => ['topicId' => 'id']],
            [['wordId'], 'exist', 'skipOnError' => true, 'targetClass' => MWords::className(), 'targetAttribute' => ['wordId' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'topicId' => 'Topic ID',
            'resourceId' => 'Resource ID',
            'locationId' => 'Location ID',
            'wordId' => 'Word ID',
            'status' => 'Status',
            'createdAt' => 'Created At',
            'updatedAt' => 'Updated At',
            'createdBy' => 'Created By',
            'updatedBy' => 'Updated By',
        ];
    }

    /**
     * Gets query for [[MAttachments]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getMAttachments()
    {
        return $this->hasMany(MAttachments::className(), ['topicStatisticId' => 'id']);
    }

    /**
     * Gets query for [[MStatistics]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getMStatistics()
    {
        return $this->hasMany(MStatistics::className(), ['topicStaticId' => 'id']);
    }

    /**
     * Gets query for [[Resource]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getResource()
    {
        return $this->hasOne(MResources::className(), ['id' => 'resourceId']);
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

    /**
     * Gets query for [[Word]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getWord()
    {
        return $this->hasOne(MWords::className(), ['id' => 'wordId']);
    }
}
