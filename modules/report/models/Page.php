<?php
namespace app\modules\report\models;

use Yii;

/**
 * This is the model class for table "page".
 *
 * @property int $id
 * @property int $sectionId
 * @property string|null $title
 * @property int|null $status
 * @property int|null $createdAt
 * @property int|null $updatedAt
 * @property int|null $createdBy
 * @property int|null $updatedBy
 *
 * @property Section $section
 * @property PageElement[] $pageElements
 */
class Page extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'page';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            //[['sectionId'], 'required'],
            [['title'], 'required'],
            [['sectionId', 'status', 'createdAt', 'updatedAt', 'createdBy', 'updatedBy'], 'integer'],
            [['title'], 'string', 'max' => 255],
            [['sectionId'], 'exist', 'skipOnError' => true, 'targetClass' => Section::className(), 'targetAttribute' => ['sectionId' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'sectionId' => Yii::t('app', 'Section ID'),
            'title' => Yii::t('app', 'Titulo de Cabecera'),
            'status' => Yii::t('app', 'Status'),
            'createdAt' => Yii::t('app', 'Created At'),
            'updatedAt' => Yii::t('app', 'Updated At'),
            'createdBy' => Yii::t('app', 'Created By'),
            'updatedBy' => Yii::t('app', 'Updated By'),
        ];
    }

    /**
     * Gets query for [[Section]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSection()
    {
        return $this->hasOne(Section::className(), ['id' => 'sectionId']);
    }

    /**
     * Gets query for [[PageElements]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPageElements()
    {
        return $this->hasMany(PageElement::className(), ['pageId' => 'id']);
    }
}
