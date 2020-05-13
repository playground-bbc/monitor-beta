<?php

namespace app\modules\topic\models;

use Yii;

/**
 * This is the model class for table "m_locations".
 *
 * @property int $id
 * @property int|null $parentId
 * @property string $name
 * @property int $woeid
 * @property int|null $createdAt
 * @property int|null $updatedAt
 * @property int|null $createdBy
 * @property int|null $updatedBy
 *
 * @property MTopicsLocation[] $mTopicsLocations
 */
class MLocations extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'm_locations';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['parentId', 'woeid', 'createdAt', 'updatedAt', 'createdBy', 'updatedBy'], 'integer'],
            [['name', 'woeid'], 'required'],
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
            'parentId' => Yii::t('app', 'Parent ID'),
            'name' => Yii::t('app', 'Name'),
            'woeid' => Yii::t('app', 'Woeid'),
            'createdAt' => Yii::t('app', 'Created At'),
            'updatedAt' => Yii::t('app', 'Updated At'),
            'createdBy' => Yii::t('app', 'Created By'),
            'updatedBy' => Yii::t('app', 'Updated By'),
        ];
    }

    /**
     * Gets query for [[MTopicsLocations]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getMTopicsLocations()
    {
        return $this->hasMany(MTopicsLocation::className(), ['locationId' => 'id']);
    }
}
