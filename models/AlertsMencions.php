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



    /**
     * [getShareFaceBookPost return share count]
     * @return [string] [description]
     */
    public function getShareFaceBookPost()
    {

        $db = \Yii::$app->db;
        $alertMentions = $db->cache(function ($db) {
            return $this->find()->where(['alertId' => $this->alertId,'resourcesId' => $this->resourcesId])->all();
        },60);
        
        $shares = 0;
        foreach ($alertMentions as $alertMention) {
            $shares +=  $alertMention->mention_data['shares'];
        }

        return (string) $shares;
    }
    /**
     * [getLikesFacebookComments return likes coment]
     * @return [type] [description]
     */
    public function getLikesFacebookComments()
    {
        $db = \Yii::$app->db;
        $alertMentions = $db->cache(function ($db){
            return $this->find()->where(['alertId' => $this->alertId,'resourcesId' => $this->resourcesId])->all();
        },60);
       $likes_count = 0;

       foreach ($alertMentions as $alertMention) {
            if($alertMention->mentionsCount){
                foreach ($alertMention->mentions as $mentions => $mention) {
                    $likes_count += $mention->mention_data['like_count'];
                }
            }
        }

        return (string) $likes_count;

    }
    /**
     * [getTotal total mencion]
     * @return [type] [description]
     */
    public function getTotal()
    {
        $db = \Yii::$app->db;
        $alertMentions = $db->cache(function ($db){
            return $this->find()->where(['alertId' => $this->alertId,'resourcesId' => $this->resourcesId])->all();
        },60);
        $total = 0;
        foreach ($alertMentions as $alertMention) {
            $total += $alertMention->mentionsCount;
        }
        return (string) $total;

    }
    /**
     * [getLikesInstagramPost total likes post instagram]
     * @return [type] [description]
     */
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
    /**
     * [getTwitterRetweets count retweets]
     * @return [type] [description]
     */
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
    /**
     * [getTwitterLikes return likes or favorite twitter]
     * @return [type] [description]
     */
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
    /**
     * [getTwitterTotal twitter total]
     * @return [type]        [description]
     */
    public function getTwitterTotal()
    {
        $alertMentions = $this->find()->where(['alertId' => $this->alertId,'resourcesId' => $this->resourcesId,'type' => 'tweet'])->all();
        $total = 0;
        foreach ($alertMentions as $alertMention) {
            if($alertMention->mentionsCount){
                $total += $alertMention->mentionsCount;

            }

        }
        // count values in document
        $alertMentionsDocuments = $this->find()->where(['alertId' => $this->alertId,'type'=>'document'])->all();
        foreach ($alertMentionsDocuments as $alertMentionsDocument) {
            if($alertMentionsDocument->mentionsCount){
                $total += $this->getCountDocumentByResource('TWITTER',$alertMentionsDocument->id);
            }
        }

        return (string) $total;
    }

    /**
     * [getCountDocumentByResource return mention document by resource name]
     * @param  [type] $resource        [description]
     * @param  [type] $alert_mentionId [description]
     * @return [type]                  [description]
     */
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


    public function getTopPostFacebookInterations()
    {
        $connection = \yii::$app->getDb();
        $command = $connection->createCommand("
            SELECT id, title,url, JSON_EXTRACT(mention_data, '$.shares') AS shares 
            FROM `alerts_mencions` 
            WHERE alertId =:alertId AND mention_data IS NOT NULL AND resourcesId = :resourcesId  
            ORDER BY shares DESC LIMIT 3", 
            [':resourcesId' => $this->resourcesId,':alertId' => $this->alertId]
        );

        $results = $command->queryAll();  
        $data = [];

        for ($r=0; $r < sizeof($results) ; $r++) { 
            $title = \app\helpers\StringHelper::ensureRightPoints(\app\helpers\StringHelper::substring($results[$r]['title'],0,30));
            $title .= ' (FB)';

            $mentions = \app\models\Mentions::find()->where(['alert_mentionId' => $results[$r]['id']])->all();
            $likes_count = 0;
            $count = count($mentions);

            if($count){
                foreach ($mentions as $mention) {
                    $likes = $mention->mention_data['like_count'];
                    $likes_count += $likes;
                }

            }

            $data[] = array($title,(int)$results[$r]['shares'],0,(int)$likes_count,(int)$count,$results[$r]['url']);
        }
        return $data;
    }

    public function getTopPostInstagramInterations()
    {
        $connection = \yii::$app->getDb();
        $command = $connection->createCommand("
            SELECT id, title,url, JSON_EXTRACT(mention_data, '$.like_count') AS like_count 
            FROM `alerts_mencions` 
            WHERE alertId =:alertId AND mention_data IS NOT NULL AND resourcesId = :resourcesId  
            ORDER BY like_count DESC LIMIT 3", 
            [':resourcesId' => $this->resourcesId,':alertId' => $this->alertId]
        );

        $results = $command->queryAll();  
        $data = [];

        for ($r=0; $r < sizeof($results) ; $r++) { 
            $title = \app\helpers\StringHelper::ensureRightPoints(\app\helpers\StringHelper::substring($results[$r]['title'],0,30));
            $title .= ' (IG)';
            $mentions = \app\models\Mentions::find()->where(['alert_mentionId' => $results[$r]['id']])->all();
            $likes_count = 0;
            $count = count($mentions);

            if($count){
                foreach ($mentions as $mention) {
                    $likes = $mention->mention_data['like_count'];
                    $likes_count += $likes;
                }

            }

            $data[] = array($title,0,(int)$results[$r]['like_count'],(int)$likes_count,(int)$count,$results[$r]['url']);

        }
        return $data;
    }

}
