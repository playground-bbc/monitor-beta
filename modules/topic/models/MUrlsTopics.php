<?php

namespace app\modules\topic\models;

use Yii;

/**
 * This is the model class for table "m_urls_topics".
 *
 * @property int $id
 * @property int $topicId
 * @property string|null $url
 * @property int|null $status
 * @property int|null $createdAt
 * @property int|null $updatedAt
 * @property int|null $createdBy
 * @property int|null $updatedBy
 *
 * @property MTopics $topic
 */
class MUrlsTopics extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'm_urls_topics';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['topicId'], 'required'],
            [['topicId', 'status', 'createdAt', 'updatedAt', 'createdBy', 'updatedBy'], 'integer'],
            [['url'], 'string', 'max' => 255],
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
            'url' => Yii::t('app', 'Url'),
            'status' => Yii::t('app', 'Status'),
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
}
