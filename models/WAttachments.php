<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "w_attachments".
 *
 * @property int $id
 * @property int $content_id
 * @property string|null $title
 * @property string|null $type
 * @property string|null $src_url
 * @property int|null $createdAt
 * @property int|null $updatedAt
 * @property int|null $createdBy
 * @property int|null $updatedBy
 *
 * @property WContent $content
 */
class WAttachments extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'w_attachments';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['content_id'], 'required'],
            [['content_id', 'createdAt', 'updatedAt', 'createdBy', 'updatedBy'], 'integer'],
            [['title', 'type', 'src_url'], 'string', 'max' => 255],
            [['content_id'], 'exist', 'skipOnError' => true, 'targetClass' => WContent::className(), 'targetAttribute' => ['content_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'content_id' => 'Content ID',
            'title' => 'Title',
            'type' => 'Type',
            'src_url' => 'Src Url',
            'createdAt' => 'Created At',
            'updatedAt' => 'Updated At',
            'createdBy' => 'Created By',
            'updatedBy' => 'Updated By',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getContent()
    {
        return $this->hasOne(WContent::className(), ['id' => 'content_id']);
    }
}
