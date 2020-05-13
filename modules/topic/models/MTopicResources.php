<?php

namespace app\modules\topic\models;

use Yii;

/**
 * This is the model class for table "m_topic_resources".
 *
 * @property int $id
 * @property int $topicId
 * @property int $resourceId
 * @property int|null $createdAt
 * @property int|null $updatedAt
 * @property int|null $createdBy
 * @property int|null $updatedBy
 *
 * @property MTopics $topic
 * @property MResources $resource
 */
class MTopicResources extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'm_topic_resources';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['topicId', 'resourceId'], 'required'],
            [['topicId', 'resourceId', 'createdAt', 'updatedAt', 'createdBy', 'updatedBy'], 'integer'],
            [['topicId'], 'exist', 'skipOnError' => true, 'targetClass' => MTopics::className(), 'targetAttribute' => ['topicId' => 'id']],
            [['resourceId'], 'exist', 'skipOnError' => true, 'targetClass' => MResources::className(), 'targetAttribute' => ['resourceId' => 'id']],
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
            'resourceId' => Yii::t('app', 'Resource ID'),
            'createdAt' => Yii::t('app', 'Created At'),
            'updatedAt' => Yii::t('app', 'Updated At'),
            'createdBy' => Yii::t('app', 'Created By'),
            'updatedBy' => Yii::t('app', 'Updated By'),
        ];
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
     * Gets query for [[Resource]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getResource()
    {
        return $this->hasOne(MResources::className(), ['id' => 'resourceId']);
    }
}
