<?php

namespace app\modules\wordlists\models;

use Yii;

/**
 * This is the model class for table "keywords".
 *
 * @property int $id
 * @property int $dictionaryId
 * @property string $name
 * @property int $createdAt
 * @property int $updatedAt
 * @property int $createdBy
 * @property int $updatedBy
 *
 * @property AlertsKeywords[] $alertsKeywords
 * @property Dictionaries $dictionary
 */
class Keywords extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'keywords';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['dictionaryId', 'createdAt', 'updatedAt', 'createdBy', 'updatedBy'], 'integer'],
            [['name'], 'string', 'max' => 255],
            [['dictionaryId'], 'exist', 'skipOnError' => true, 'targetClass' => Dictionaries::className(), 'targetAttribute' => ['dictionaryId' => 'id']],
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
     * @return \yii\db\ActiveQuery
     */
    public function getKeywordsMentions()
    {
        return $this->hasMany(KeywordsMentions::className(), ['keywordId' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDictionary()
    {
        return $this->hasOne(Dictionaries::className(), ['id' => 'dictionaryId']);
    }
}
