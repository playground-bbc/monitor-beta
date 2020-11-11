<?php

namespace app\modules\report\models;

use Yii;

/**
 * This is the model class for table "page_element".
 *
 * @property int $id
 * @property int $pageId
 * @property string|null $name
 * @property string|null $value
 * @property int|null $status
 * @property int|null $createdAt
 * @property int|null $updatedAt
 * @property int|null $createdBy
 * @property int|null $updatedBy
 *
 * @property Page $page
 */
class PageElement extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'page_element';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['pageId','name','value'], 'required'],
            [['pageId', 'status', 'createdAt', 'updatedAt', 'createdBy', 'updatedBy'], 'integer'],
            [['name', 'value'], 'string', 'max' => 255],
            [['pageId'], 'exist', 'skipOnError' => true, 'targetClass' => Page::className(), 'targetAttribute' => ['pageId' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'pageId' => Yii::t('app', 'Page ID'),
            'name' => Yii::t('app', 'Name'),
            'value' => Yii::t('app', 'Value'),
            'status' => Yii::t('app', 'Status'),
            'createdAt' => Yii::t('app', 'Created At'),
            'updatedAt' => Yii::t('app', 'Updated At'),
            'createdBy' => Yii::t('app', 'Created By'),
            'updatedBy' => Yii::t('app', 'Updated By'),
        ];
    }

    /**
     * Gets query for [[Page]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPage()
    {
        return $this->hasOne(Page::className(), ['id' => 'pageId']);
    }
}
