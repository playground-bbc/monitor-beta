<?php

namespace app\modules\wordlists\models;

use Yii;

/**
 * This is the model class for table "alerts_keywords".
 *
 * @property int $id
 * @property int $alertId
 * @property int $keywordId
 * @property int|null $createdAt
 * @property int|null $updatedAt
 * @property int|null $createdBy
 * @property int|null $updatedBy
 *
 * @property Alerts $alert
 * @property Keywords $keyword
 */
class AlertsKeywords extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'alerts_keywords';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['alertId', 'keywordId'], 'required'],
            [['alertId', 'keywordId', 'createdAt', 'updatedAt', 'createdBy', 'updatedBy'], 'integer'],
            [['alertId'], 'exist', 'skipOnError' => true, 'targetClass' => Alerts::className(), 'targetAttribute' => ['alertId' => 'id']],
            [['keywordId'], 'exist', 'skipOnError' => true, 'targetClass' => Keywords::className(), 'targetAttribute' => ['keywordId' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'alertId' => Yii::t('app', 'Alert ID'),
            'keywordId' => Yii::t('app', 'Keyword ID'),
            'createdAt' => Yii::t('app', 'Created At'),
            'updatedAt' => Yii::t('app', 'Updated At'),
            'createdBy' => Yii::t('app', 'Created By'),
            'updatedBy' => Yii::t('app', 'Updated By'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAlert()
    {
        return $this->hasOne(Alerts::className(), ['id' => 'alertId']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getKeyword()
    {
        return $this->hasOne(Keywords::className(), ['id' => 'keywordId']);
    }
}
