<?php

namespace app\modules\report\models;

use Yii;

/**
 * This is the model class for table "section".
 *
 * @property int $id
 * @property int $presentationId
 * @property int $typeSection
 * @property string|null $head_title
 * @property int|null $status
 * @property int|null $createdAt
 * @property int|null $updatedAt
 * @property int|null $createdBy
 * @property int|null $updatedBy
 *
 * @property Page[] $pages
 * @property Presentation $presentation
 * @property SectionType $typeSection0
 */
class Section extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'section';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['presentationId', 'typeSection','head_title'], 'required'],
            [['presentationId', 'typeSection', 'status', 'createdAt', 'updatedAt', 'createdBy', 'updatedBy'], 'integer'],
            [['head_title'], 'string', 'max' => 255],
            [['presentationId'], 'exist', 'skipOnError' => true, 'targetClass' => Presentation::className(), 'targetAttribute' => ['presentationId' => 'id']],
            [['typeSection'], 'exist', 'skipOnError' => true, 'targetClass' => SectionType::className(), 'targetAttribute' => ['typeSection' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'presentationId' => Yii::t('app', 'Presentation ID'),
            'typeSection' => Yii::t('app', 'Type Section'),
            'head_title' => Yii::t('app', 'Titulo de Portada'),
            'status' => Yii::t('app', 'Status'),
            'createdAt' => Yii::t('app', 'Created At'),
            'updatedAt' => Yii::t('app', 'Updated At'),
            'createdBy' => Yii::t('app', 'Created By'),
            'updatedBy' => Yii::t('app', 'Updated By'),
        ];
    }

    /**
     * Gets query for [[Pages]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPages()
    {
        return $this->hasMany(Page::className(), ['sectionId' => 'id']);
    }

    /**
     * Gets query for [[Presentation]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPresentation()
    {
        return $this->hasOne(Presentation::className(), ['id' => 'presentationId']);
    }

    /**
     * Gets query for [[TypeSection0]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTypeSection0()
    {
        return $this->hasOne(SectionType::className(), ['id' => 'typeSection']);
    }
}
