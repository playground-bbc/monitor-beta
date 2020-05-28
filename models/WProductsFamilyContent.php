<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "w_products_family_content".
 *
 * @property int $id
 * @property int $contentId
 * @property int $serieId
 * @property int|null $createdAt
 * @property int|null $updatedAt
 * @property int|null $createdBy
 * @property int|null $updatedBy
 *
 * @property WContent $content
 * @property ProductsSeries $serie
 */
class WProductsFamilyContent extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'w_products_family_content';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['contentId', 'serieId'], 'required'],
            [['contentId', 'serieId', 'createdAt', 'updatedAt', 'createdBy', 'updatedBy'], 'integer'],
            [['contentId'], 'exist', 'skipOnError' => true, 'targetClass' => WContent::className(), 'targetAttribute' => ['contentId' => 'id']],
            [['serieId'], 'exist', 'skipOnError' => true, 'targetClass' => ProductsSeries::className(), 'targetAttribute' => ['serieId' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'contentId' => Yii::t('app', 'Content ID'),
            'serieId' => Yii::t('app', 'Serie ID'),
            'createdAt' => Yii::t('app', 'Created At'),
            'updatedAt' => Yii::t('app', 'Updated At'),
            'createdBy' => Yii::t('app', 'Created By'),
            'updatedBy' => Yii::t('app', 'Updated By'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getContent()
    {
        return $this->hasOne(WContent::className(), ['id' => 'contentId']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSerie()
    {
        return $this->hasOne(ProductsSeries::className(), ['id' => 'serieId']);
    }
}
