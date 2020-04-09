<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
/**
 * This is the model class for table "w_content".
 *
 * @property int $id
 * @property int $type_content_id
 * @property int $resource_id
 * @property int $content_id
 * @property string|null $message
 * @property string|null $permalink
 * @property string|null $image_url
 * @property int|null $timespan
 * @property int|null $createdAt
 * @property int|null $updatedAt
 * @property int|null $createdBy
 * @property int|null $updatedBy
 *
 * @property WAttachments[] $wAttachments
 * @property Resources $resource
 * @property WTypeContent $typeContent
 * @property WInsights[] $wInsights
 */
class WContent extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'w_content';
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
            [['type_content_id', 'resource_id','content_id'], 'required'],
            [['type_content_id', 'resource_id', 'timespan', 'createdAt', 'updatedAt', 'createdBy', 'updatedBy'], 'integer'],
            [['message', 'permalink', 'image_url'], 'string'],
            [['resource_id'], 'exist', 'skipOnError' => true, 'targetClass' => Resources::className(), 'targetAttribute' => ['resource_id' => 'id']],
            [['type_content_id'], 'exist', 'skipOnError' => true, 'targetClass' => WTypeContent::className(), 'targetAttribute' => ['type_content_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'type_content_id' => 'Type Content ID',
            'resource_id' => 'Resource ID',
            'message' => 'Message',
            'permalink' => 'Permalink',
            'image_url' => 'Image Url',
            'timespan' => 'Timespan',
            'createdAt' => 'Created At',
            'updatedAt' => 'Updated At',
            'createdBy' => 'Created By',
            'updatedBy' => 'Updated By',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getWAttachments()
    {
        return $this->hasMany(WAttachments::className(), ['content_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getResource()
    {
        return $this->hasOne(Resources::className(), ['id' => 'resource_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTypeContent()
    {
        return $this->hasOne(WTypeContent::className(), ['id' => 'type_content_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getWInsights()
    {
        return $this->hasMany(WInsights::className(), ['content_id' => 'id']);
    }
}
