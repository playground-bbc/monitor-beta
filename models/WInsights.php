<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "w_insights".
 *
 * @property int $id
 * @property int $content_id
 * @property string|null $name
 * @property string|null $title
 * @property string|null $description
 * @property string|null $insights_id
 * @property string|null $period
 * @property int|null $value
 * @property int|null $end_time
 * @property int|null $createdAt
 * @property int|null $updatedAt
 * @property int|null $createdBy
 * @property int|null $updatedBy
 *
 * @property WContent $content
 */
class WInsights extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'w_insights';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['content_id'], 'required'],
            [['content_id', 'value', 'end_time', 'createdAt', 'updatedAt', 'createdBy', 'updatedBy'], 'integer'],
            [['name', 'title', 'description', 'insights_id', 'period'], 'string', 'max' => 255],
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
            'name' => 'Name',
            'title' => 'Title',
            'description' => 'Description',
            'insights_id' => 'Insights ID',
            'period' => 'Period',
            'value' => 'Value',
            'end_time' => 'End Time',
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
