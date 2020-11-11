<?php

namespace app\modules\report\models;

use Yii;

/**
 * This is the model class for table "section_type".
 *
 * @property int $id
 * @property string $name
 * @property int|null $status
 * @property int|null $createdAt
 * @property int|null $updatedAt
 * @property int|null $createdBy
 * @property int|null $updatedBy
 *
 * @property Section[] $sections
 */
class SectionType extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'section_type';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['status', 'createdAt', 'updatedAt', 'createdBy', 'updatedBy'], 'integer'],
            [['name'], 'string', 'max' => 255],
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
            'status' => Yii::t('app', 'Status'),
            'createdAt' => Yii::t('app', 'Created At'),
            'updatedAt' => Yii::t('app', 'Updated At'),
            'createdBy' => Yii::t('app', 'Created By'),
            'updatedBy' => Yii::t('app', 'Updated By'),
        ];
    }

    /**
     * Gets query for [[Sections]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSections()
    {
        return $this->hasMany(Section::className(), ['typeSection' => 'id']);
    }
}
