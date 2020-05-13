<?php

namespace app\modules\topic\models;

use Yii;

/**
 * This is the model class for table "m_dictionaries".
 *
 * @property int $id
 * @property string $name
 * @property string|null $color
 * @property int|null $createdAt
 * @property int|null $updatedAt
 * @property int|null $createdBy
 * @property int|null $updatedBy
 *
 * @property MKeywords[] $mKeywords
 * @property MTopicsDictionary[] $mTopicsDictionaries
 */
class MDictionaries extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'm_dictionaries';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['createdAt', 'updatedAt', 'createdBy', 'updatedBy'], 'integer'],
            [['name', 'color'], 'string', 'max' => 45],
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
            'color' => Yii::t('app', 'Color'),
            'createdAt' => Yii::t('app', 'Created At'),
            'updatedAt' => Yii::t('app', 'Updated At'),
            'createdBy' => Yii::t('app', 'Created By'),
            'updatedBy' => Yii::t('app', 'Updated By'),
        ];
    }

    /**
     * Gets query for [[MKeywords]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getMKeywords()
    {
        return $this->hasMany(MKeywords::className(), ['dictionaryId' => 'id']);
    }

    /**
     * Gets query for [[MTopicsDictionaries]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getMTopicsDictionaries()
    {
        return $this->hasMany(MTopicsDictionary::className(), ['dictionaryID' => 'id']);
    }
}
