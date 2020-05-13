<?php

namespace app\modules\topic\models;

use Yii;

/**
 * This is the model class for table "m_resources".
 *
 * @property int $id
 * @property string $name
 * @property int|null $createdAt
 * @property int|null $updatedAt
 * @property int|null $createdBy
 * @property int|null $updatedBy
 *
 * @property MTopicResources[] $mTopicResources
 * @property MTopicsStadistics[] $mTopicsStadistics
 */
class MResources extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'm_resources';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['createdAt', 'updatedAt', 'createdBy', 'updatedBy'], 'integer'],
            [['name'], 'string', 'max' => 40],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'name' => Yii::t('app', 'Name'),
            'createdAt' => Yii::t('app', 'Created At'),
            'updatedAt' => Yii::t('app', 'Updated At'),
            'createdBy' => Yii::t('app', 'Created By'),
            'updatedBy' => Yii::t('app', 'Updated By'),
        ];
    }

    /**
     * Gets query for [[MTopicResources]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getMTopicResources()
    {
        return $this->hasMany(MTopicResources::className(), ['resourceId' => 'id']);
    }

    /**
     * Gets query for [[MTopicsStadistics]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getMTopicsStadistics()
    {
        return $this->hasMany(MTopicsStadistics::className(), ['resourceId' => 'id']);
    }


    /**
     * Gets query for [[MTopicsStadisticsCount]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCountStadistics()
    {
        return $this->hasMany(MTopicsStadistics::className(), ['resourceId' => 'id'])->count();
    }
}
