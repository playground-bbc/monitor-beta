<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "alerts_mencions_words".
 *
 * @property int $id
 * @property int $alert_mentionId
 * @property int|null $mention_socialId
 * @property string|null $name
 * @property int|null $weight
 * @property int|null $createdAt
 * @property int|null $updatedAt
 * @property int|null $createdBy
 * @property int|null $updatedBy
 *
 * @property AlertsMencions $alertMention
 */
class AlertsMencionsWords extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'alerts_mencions_words';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['alert_mentionId','name'], 'required'],
            [['alert_mentionId', 'mention_socialId', 'weight', 'createdAt', 'updatedAt', 'createdBy', 'updatedBy'], 'integer'],
            [['name'], 'string', 'max' => 255],
            [['alert_mentionId'], 'exist', 'skipOnError' => true, 'targetClass' => AlertsMencions::className(), 'targetAttribute' => ['alert_mentionId' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'alert_mentionId' => Yii::t('app', 'Alert Mention ID'),
            'mention_socialId' => Yii::t('app', 'Mention Social ID'),
            'name' => Yii::t('app', 'Name'),
            'weight' => Yii::t('app', 'Weight'),
            'createdAt' => Yii::t('app', 'Created At'),
            'updatedAt' => Yii::t('app', 'Updated At'),
            'createdBy' => Yii::t('app', 'Created By'),
            'updatedBy' => Yii::t('app', 'Updated By'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAlertMention()
    {
        return $this->hasOne(AlertsMencions::className(), ['id' => 'alert_mentionId']);
    }
}
