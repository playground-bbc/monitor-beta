<?php

namespace app\modules\topic\models;

use Yii;

/**
 * This is the model class for table "m_attachments".
 *
 * @property int $id
 * @property int $topicStatisticId
 * @property string|null $src_url
 * @property int|null $createdAt
 * @property int|null $updatedAt
 * @property int|null $createdBy
 * @property int|null $updatedBy
 *
 * @property MTopicsStadistics $topicStatistic
 */
class MAttachments extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'm_attachments';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['topicStatisticId'], 'required'],
            [['topicStatisticId', 'createdAt', 'updatedAt', 'createdBy', 'updatedBy'], 'integer'],
            [['src_url'], 'string'],
            [['topicStatisticId'], 'exist', 'skipOnError' => true, 'targetClass' => MTopicsStadistics::className(), 'targetAttribute' => ['topicStatisticId' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'topicStatisticId' => 'Topic Statistic ID',
            'src_url' => 'Src Url',
            'createdAt' => 'Created At',
            'updatedAt' => 'Updated At',
            'createdBy' => 'Created By',
            'updatedBy' => 'Updated By',
        ];
    }

    /**
     * Gets query for [[TopicStatistic]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTopicStatistic()
    {
        return $this->hasOne(MTopicsStadistics::className(), ['id' => 'topicStatisticId']);
    }
}
