<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "alert_config".
 *
 * @property int $id
 * @property int $alertId
 * @property string $product_description
 * @property string $competitors
 * @property string $countries
 * @property int $start_date
 * @property int $end_date
 * @property int $createdAt
 * @property int $updatedAt
 * @property int $createdBy
 * @property int $updatedBy
 *
 * @property AlertconfigSources[] $alertconfigSources
 */
class AlertConfig extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'alert_config';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['product_description', 'competitors', 'countries','uudi'], 'required'],
            [['alertId', 'createdAt', 'updatedAt', 'createdBy', 'updatedBy'], 'integer'],
            [['product_description', 'competitors', 'countries'], 'string', 'max' => 40],
            [['alertId'], 'exist', 
              'skipOnError' => true, 
              'targetClass' => Alerts::className(), 
              'targetAttribute' => ['alertId' => 'id']]
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
            'product_description' => Yii::t('app', 'Product Description'),
            'competitors' => Yii::t('app', 'Competitors'),
            'countries' => Yii::t('app', 'Countries'),
            'start_date' => Yii::t('app', 'Start Date'),
            'end_date' => Yii::t('app', 'End Date'),
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
    public function getConfigSources()
    {
        return $this->hasMany(AlertconfigSources::className(), ['alertconfigId' => 'id']);
    }
}
