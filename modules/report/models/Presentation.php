<?php

namespace app\modules\report\models;

use Yii;
use app\models\Users;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "presentation".
 *
 * @property int $id
 * @property int $userId
 * @property string $name
 * @property string $head_title
 * @property string $title
 * @property int $date
 * @property string|null $url_sheet
 * @property string|null $url_presentation
 * @property int|null $status
 * @property int|null $updated
 * @property int|null $createdAt
 * @property int|null $updatedAt
 * @property int|null $createdBy
 * @property int|null $updatedBy
 *
 * @property Users $user
 * @property Section[] $sections
 */
class Presentation extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'presentation';
    }

    public function behaviors()
    {
        return [
            'timestamp' => [
                'class' => TimestampBehavior::className(),
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['createdAt','updatedAt'],
                    ActiveRecord::EVENT_BEFORE_UPDATE => ['updatedAt'],
                ],
                'value' => function() { return date('U');  },
            ],
            
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['userId', 'name', 'head_title', 'title'], 'required'],
            [['date'], 'required'],
            //[['date'], 'filter', 'filter' => [$this, 'normalizeDate']],
            //['date', 'date', 'timestampAttribute' => 'date'],
            [['userId', 'status', 'updated', 'createdAt', 'updatedAt', 'createdBy', 'updatedBy'], 'integer'],
            [['name', 'head_title', 'title', 'url_sheet', 'url_presentation'], 'string', 'max' => 255],
            [['userId'], 'exist', 'skipOnError' => true, 'targetClass' => Users::className(), 'targetAttribute' => ['userId' => 'id']],
        ];
    }

    
    

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'userId' => Yii::t('app', 'User ID'),
            'name' => Yii::t('app', 'Nombre de la Presentación'),
            'head_title' => Yii::t('app', 'Título de Portada'),
            'title' => Yii::t('app', 'Título de Cabecera'),
            'date' => Yii::t('app', 'Fecha del Reporte'),
            'url_sheet' => Yii::t('app', 'Url Sheet'),
            'url_presentation' => Yii::t('app', 'Url Presentation'),
            'status' => Yii::t('app', 'Status'),
            'updated' => Yii::t('app', 'Updated'),
            'createdAt' => Yii::t('app', 'Created At'),
            'updatedAt' => Yii::t('app', 'Updated At'),
            'createdBy' => Yii::t('app', 'Created By'),
            'updatedBy' => Yii::t('app', 'Updated By'),
        ];
    }

    /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(Users::className(), ['id' => 'userId']);
    }

    /**
     * Gets query for [[Sections]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSections()
    {
        return $this->hasMany(Section::className(), ['presentationId' => 'id']);
    }
}
