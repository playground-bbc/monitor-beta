<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "w_type_content".
 *
 * @property int $id
 * @property string|null $name
 * @property int|null $createdAt
 * @property int|null $updatedAt
 * @property int|null $createdBy
 * @property int|null $updatedBy
 *
 * @property WContent[] $wContents
 */
class WTypeContent extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'w_type_content';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['createdAt', 'updatedAt', 'createdBy', 'updatedBy'], 'integer'],
            [['name'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'createdAt' => 'Created At',
            'updatedAt' => 'Updated At',
            'createdBy' => 'Created By',
            'updatedBy' => 'Updated By',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getWContents()
    {
        return $this->hasMany(WContent::className(), ['type_content_id' => 'id']);
    }
}
