<?php

namespace app\models;

use Yii;
use app\modules\wordlists\models\Keywords;

/**
 * This is the model class for table "keywords_mentions".
 *
 * @property int $id
 * @property int $keywordId
 * @property int $mentionId
 * @property int $createdAt
 * @property int $updatedAt
 * @property int $createdBy
 * @property int $updatedBy
 *
 * @property Keywords $keyword
 * @property Mentions $mention
 */
class KeywordsMentions extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'keywords_mentions';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['keywordId', 'mentionId'], 'required'],
            [['keywordId', 'mentionId', 'createdAt', 'updatedAt', 'createdBy', 'updatedBy'], 'integer'],
            [['keywordId'], 'exist', 'skipOnError' => true, 'targetClass' => Keywords::className(), 'targetAttribute' => ['keywordId' => 'id']],
            [['mentionId'], 'exist', 'skipOnError' => true, 'targetClass' => Mentions::className(), 'targetAttribute' => ['mentionId' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'keywordId' => 'Keyword ID',
            'mentionId' => 'Mention ID',
            'createdAt' => 'Created At',
            'updatedAt' => 'Updated At',
            'createdBy' => 'Created By',
            'updatedBy' => 'Updated By',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getKeyword()
    {
        return $this->hasOne(Keywords::className(), ['id' => 'keywordId']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMention()
    {
        return $this->hasOne(Mentions::className(), ['id' => 'mentionId']);
    }
}
