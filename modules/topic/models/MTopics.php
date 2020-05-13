<?php

namespace app\modules\topic\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;


/**
 * This is the model class for table "m_topics".
 *
 * @property int $id
 * @property int $userId
 * @property string|null $name
 * @property int|null $status
 * @property int|null $end_date
 * @property int|null $resourceId | only class 
 * @property int|null $locationId | only class 
 * @property int|null $dictionaryId | only class 
 * @property int|null $urls | only class 
 * @property int|null $createdAt
 * @property int|null $updatedAt
 * @property int|null $createdBy
 * @property int|null $updatedBy
 *
 * @property MTopicResources[] $mTopicResources
 * @property Users $user
 * @property MTopicsDictionary[] $mTopicsDictionaries
 * @property MTopicsLocation[] $mTopicsLocations
 * @property MTopicsStadistics[] $mTopicsStadistics
 * @property MUrlsTopics[] $mUrlsTopics
 */
class MTopics extends \yii\db\ActiveRecord
{
    public $resourceId;
    public $locationId;
    public $dictionaryId;
    public $urls;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'm_topics';
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
            [['userId','name','end_date','resourceId'], 'required'],
            [['userId', 'status', 'createdAt', 'updatedAt', 'createdBy', 'updatedBy'], 'integer'],
            [['name'], 'string', 'max' => 255],
            [['userId'], 'exist', 'skipOnError' => true, 'targetClass' => \app\models\Users::className(), 'targetAttribute' => ['userId' => 'id']],
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
            'name' => Yii::t('app', 'Nombre'),
            'status' => Yii::t('app', 'Status'),
            'end_date' => Yii::t('app', 'Fecha Final'),
            'locationId' => Yii::t('app', 'Pais para Twitter'),
            'dictionaryId' => Yii::t('app', 'Diccionarios Drive'),
            'urls' => Yii::t('app', 'Urls'),
            'resourceId' => Yii::t('app', 'Recursos Sociales'),
            'end_date' => Yii::t('app', 'Fecha Final'),
            'createdAt' => Yii::t('app', 'Created At'),
            'updatedAt' => Yii::t('app', 'Updated At'),
            'createdBy' => Yii::t('app', 'Created By'),
            'updatedBy' => Yii::t('app', 'Updated By'),
        ];
    }

    /**
     * Gets query for [[MTopicResources]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getMTopicResources()
    {
        return $this->hasMany(MTopicResources::className(), ['topicId' => 'id']);
    }

    public function getSources()
    {
        return $this->hasMany(MResources::className(), ['id' => 'resourceId'])
            ->viaTable('m_topic_resources', ['topicId' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getResourcesIds()
    {
        $sources = $this->mTopicResources;
        // set resources id select2
        $selectIds = [];
        foreach ($sources as $source) {
          $selectIds[] =  $source->resource->id;
        }   
        return $selectIds;                  
    }

    /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(\app\models\Users::className(), ['id' => 'userId']);
    }

    /**
     * Gets query for [[MTopicsDictionaries]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getMTopicsDictionaries()
    {
        return $this->hasMany(MTopicsDictionary::className(), ['topicId' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDictionaries()
    {
        $topicsDictionaries = $this->mTopicsDictionaries;
        // set resources id select2
        $selectIds = [];
        foreach ($topicsDictionaries as $topicsDictionarie) {
          $selectIds[] =  $topicsDictionarie->dictionary->id;
        }   
        return $selectIds;                  
    }

    /**
     * Gets query for [[MTopicsLocations]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getMTopicsLocations()
    {
        return $this->hasMany(MTopicsLocation::className(), ['topicId' => 'id']);
    }

    /**
     * Gets query for [[MWords]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getMWords()
    {
        return $this->hasMany(MWords::className(), ['topicId' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLocations()
    {
        $locations = $this->mTopicsLocations;
        // set resources id select2
        $selectIds = [];
        foreach ($locations as $value) {
          $selectIds[] =  $value->location->id;
        }   
        return $selectIds;                  
    }

    /**
     * Gets query for [[MTopicsStadistics]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getMTopicsStadistics()
    {
        return $this->hasMany(MTopicsStadistics::className(), ['topicId' => 'id']);
    }

    /**
     * Gets query for [[MUrlsTopics]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getMUrlsTopics()
    {
        return $this->hasMany(MUrlsTopics::className(), ['topicId' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUrlsTopics()
    {
        $urls = $this->mUrlsTopics;
        // set resources id select2
        $selectIds = [];
        foreach ($urls as $url) {
          $selectIds[] =  $url->url;
        }   
        return $selectIds;                  
    }

}
