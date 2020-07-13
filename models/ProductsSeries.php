<?php

namespace app\models;

use Yii;
use app\components\ProductsValidator;

/**
 * This is the model class for table "products_series".
 *
 * @property int $id
 * @property string $name
 * @property string $abbreviation_name
 * @property int $status
 * @property int $createdAt
 * @property int $updatedAt
 * @property int $createdBy
 * @property int $updatedBy
 *
 * @property ProductsFamily[] $productsFamilies
 */
class ProductsSeries extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'products_series';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['status', 'createdAt', 'updatedAt', 'createdBy', 'updatedBy'], 'integer'],
            [['name', 'abbreviation_name'], 'string', 'max' => 255],
            [['abbreviation_name'],ProductsValidator::className()],
            [['name', 'abbreviation_name'], 'required'],
        ];
    }

    public function checklength($attribute, $params){
        if(strlen($this->abbreviation_name) < 2){
            $this->addError($attribute, Yii::t('app', 'At least 1 of the field must be filled up properly'));
            return false;
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'name' => Yii::t('app', 'Name'),
            'abbreviation_name' => Yii::t('app', 'Abbreviation Name'),
            'status' => Yii::t('app', 'Status'),
            'createdAt' => Yii::t('app', 'Created At'),
            'updatedAt' => Yii::t('app', 'Updated At'),
            'createdBy' => Yii::t('app', 'Created By'),
            'updatedBy' => Yii::t('app', 'Updated By'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProductsFamilies()
    {
        return $this->hasMany(ProductsFamily::className(), ['seriesId' => 'id']);
    }
}
