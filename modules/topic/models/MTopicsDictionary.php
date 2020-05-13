<?php

namespace app\modules\topic\models;

use Yii;

/**
 * This is the model class for table "m_topics_dictionary".
 *
 * @property int $id
 * @property int $topicId
 * @property int $dictionaryID
 * @property int|null $createdAt
 * @property int|null $updatedAt
 * @property int|null $createdBy
 * @property int|null $updatedBy
 *
 * @property MDictionaries $dictionary
 * @property MTopics $topic
 */
class MTopicsDictionary extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'm_topics_dictionary';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['topicId', 'dictionaryID'], 'required'],
            [['topicId', 'dictionaryID', 'createdAt', 'updatedAt', 'createdBy', 'updatedBy'], 'integer'],
            [['dictionaryID'], 'exist', 'skipOnError' => true, 'targetClass' => MDictionaries::className(), 'targetAttribute' => ['dictionaryID' => 'id']],
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
            'dictionaryID' => Yii::t('app', 'Dictionary ID'),
            'createdAt' => Yii::t('app', 'Created At'),
            'updatedAt' => Yii::t('app', 'Updated At'),
            'createdBy' => Yii::t('app', 'Created By'),
            'updatedBy' => Yii::t('app', 'Updated By'),
        ];
    }

    /**
     * Gets query for [[Dictionary]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDictionary()
    {
        return $this->hasOne(MDictionaries::className(), ['id' => 'dictionaryID']);
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
