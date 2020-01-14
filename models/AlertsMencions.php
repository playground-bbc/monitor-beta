<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;
use yii\db\ActiveRecord;
/**
 * This is the model class for table "alerts_mencions".
 *
 * @property int $id
 * @property int $alertId
 * @property int $resourcesId
 * @property string $condition
 * @property string $type
 * @property array $product_obj
 * @property array $publication_id
 * @property array $next
 * @property array $title
 * @property array $url
 * @property int $createdAt
 * @property int $updatedAt
 * @property int $createdBy
 * @property int $updatedBy
 *
 * @property Alerts $alert
 * @property Resources $resources
 * @property Mentions[] $mentions
 */
class AlertsMencions extends \yii\db\ActiveRecord
{

    const CONDITION_WAIT   = "WAIT";
    const CONDITION_ACTIVE = "ACTIVE";
    const CONDITION_FINISH = "FINISH";
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'alerts_mencions';
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
            ],
            [
                'class'              => BlameableBehavior::className(),
                'createdByAttribute' => 'createdBy',
                'updatedByAttribute' => 'updatedBy',
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['alertId', 'resourcesId'], 'required'],
            [['alertId', 'resourcesId','since_id','max_id', 'createdAt', 'updatedAt', 'createdBy', 'updatedBy'], 'integer'],
          //  [['product_obj'], 'safe'],
            [['title', 'url'], 'string'],
            [['condition', 'type','publication_id','term_searched'], 'string', 'max' => 255],
            [['alertId'], 'exist', 'skipOnError' => true, 'targetClass' => Alerts::className(), 'targetAttribute' => ['alertId' => 'id']],
            [['resourcesId'], 'exist', 'skipOnError' => true, 'targetClass' => Resources::className(), 'targetAttribute' => ['resourcesId' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'alertId' => Yii::t('app', 'Alert ID'),
            'resourcesId' => Yii::t('app', 'Resources ID'),
            'condition' => Yii::t('app', 'Condition'),
            'type' => Yii::t('app', 'Type'),
            'product_obj' => Yii::t('app', 'Product Obj'),
            'createdAt' => Yii::t('app', 'Created At'),
            'updatedAt' => Yii::t('app', 'Updated At'),
            'createdBy' => Yii::t('app', 'Created By'),
            'updatedBy' => Yii::t('app', 'Updated By'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAlert()
    {
        return $this->hasOne(Alerts::className(), ['id' => 'alertId']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getResources()
    {
        return $this->hasOne(Resources::className(), ['id' => 'resourcesId']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMentions()
    {
        return $this->hasMany(Mentions::className(), ['alert_mentionId' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMentionsCount()
    {
        return $this->hasMany(Mentions::className(), ['alert_mentionId' => 'id'])->count();
    }


    public function getShareFaceBookPost()
    {
        $alertMentions = $this->find()->where(['alertId' => $this->alertId,'resourcesId' => $this->resourcesId])->all();
        
        
        $origin_ids = [];
        foreach ($alertMentions as $alertMention) {
            if($alertMention->mentionsCount){
                foreach ($alertMention->mentions as $mentions => $mention) {
                    if(!in_array($mention->origin_id, $origin_ids)){
                        $origin_ids[] = $mention->origin_id;
                    }
                }
            }
        }
        $shares = 0;
        $post_models = \app\models\UsersMentions::find()->where(['id' => $origin_ids])->all();
        foreach ($post_models as $post => $value) {
            $shares +=  $value['user_data']['shares']['count'];
        }

        return (string) $shares;
    }

    public function getLikesFacebookComments()
    {
       $alertMentions = $this->find()->where(['alertId' => $this->alertId,'resourcesId' => $this->resourcesId])->all();
       $likes_count = 0;

       foreach ($alertMentions as $alertMention) {
            if($alertMention->mentionsCount){
                foreach ($alertMention->mentions as $mentions => $mention) {
                    $mention_data = $mention->mention_data;
                    $likes_count += $mention_data['like_count'];
                }
            }
        }

        return (string) $likes_count;

    }

    public function getTotal()
    {
        $alertMentions = $this->find()->where(['alertId' => $this->alertId,'resourcesId' => $this->resourcesId])->all();
        $total = 0;
        foreach ($alertMentions as $alertMention) {
            $total += $alertMention->mentionsCount;
        }
        return (string) $total;

    }

    public function getLikesInstagramPost()
    {
        $like_count = 0;
        $alertMentions = $this->find()->where(['alertId' => $this->alertId,'resourcesId' => $this->resourcesId])->all();
        foreach ($alertMentions as $alertMention) {
            $mention_data = $alertMention->mention_data;
            $like_count += $mention_data['like_count'];
        }
        return (string) $like_count;
    }

    public function getTwitterRetweets()
    {
        $retweets_count = 0;
        $alertMentions = $this->find()->where(['alertId' => $this->alertId,'resourcesId' => $this->resourcesId])->all();
        foreach ($alertMentions as $alertMention) {
            if($alertMention->mentionsCount){
                foreach ($alertMention->mentions as $mentions => $mention) {
                    $mention_data = $mention->mention_data;
                    $retweets_count += $mention_data['retweet_count'];
                }

            }
        }
        return (string) $retweets_count;
    }

    public function getTwitterLikes()
    {
        $likes_count = 0;
        $alertMentions = $this->find()->where(['alertId' => $this->alertId,'resourcesId' => $this->resourcesId])->all();
        foreach ($alertMentions as $alertMention) {
            if($alertMention->mentionsCount){
                foreach ($alertMention->mentions as $mentions => $mention) {
                    $mention_data = $mention->mention_data;
                    $likes_count += $mention_data['favorite_count'];
                }

            }
        }
        return (string) $likes_count;
    }

    public function getTwitterTotal($value='')
    {
        $alertMentions = $this->find()->where(['alertId' => $this->alertId,'resourcesId' => $this->resourcesId])->all();
        $total = 0;
        foreach ($alertMentions as $alertMention) {
            if($alertMention->mentionsCount){
                $total += $alertMention->mentionsCount;

            }

        }

        $alertMentionsDocuments = $this->find()->where(['alertId' => $this->alertId,'resourcesId' => 8])->all();
        foreach ($alertMentionsDocuments as $alertMentionsDocument) {
            if($alertMentionsDocument->mentionsCount){
                $total += $this->getCountDocumentByResource('TWITTER',$alertMentionsDocument->id);
            }
        }

        return (string) $total;
    }


    public function getCountDocumentByResource($resource,$alert_mentionId)
    {
        $data = json_encode(['source'=> $resource]);
        
        $expression = new \yii\db\Expression("JSON_CONTAINS(mention_data,'{$data}')");


        $count = (new \yii\db\Query())
        ->from('mentions')
        ->where($expression)
        ->andWhere(['alert_mentionId' => $alert_mentionId])
        ->count();

        return $count;
    }

}
