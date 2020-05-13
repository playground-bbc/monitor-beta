<?php

namespace app\modules\topic\models;

use Yii;

/**
 * This is the model class for table "m_keywords".
 *
 * @property int $id
 * @property int $dictionaryId
 * @property string|null $name
 * @property int|null $createdAt
 * @property int|null $updatedAt
 * @property int|null $createdBy
 * @property int|null $updatedBy
 *
 * @property MDictionaries $dictionary
 * @property MWordsDictionaryStatistic[] $mWordsDictionaryStatistics
 */
class MKeywords extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'm_keywords';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['dictionaryId'], 'required'],
            [['dictionaryId', 'createdAt', 'updatedAt', 'createdBy', 'updatedBy'], 'integer'],
            [['name'], 'string', 'max' => 255],
            [['dictionaryId'], 'exist', 'skipOnError' => true, 'targetClass' => MDictionaries::className(), 'targetAttribute' => ['dictionaryId' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'dictionaryId' => Yii::t('app', 'Dictionary ID'),
            'name' => Yii::t('app', 'Name'),
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
        return $this->hasOne(MDictionaries::className(), ['id' => 'dictionaryId']);
    }

    /**
     * Gets query for [[MWordsDictionaryStatistics]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getMWordsDictionaryStatistics()
    {
        return $this->hasMany(MWordsDictionaryStatistic::className(), ['keywordId' => 'id']);
    }
}
