<?php

namespace app\models;

use Yii;
use Yii\helpers\ArrayHelper;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

use app\modules\wordlists\models\Dictionaries;
use app\modules\wordlists\models\Keywords;
use app\modules\wordlists\models\AlertsKeywords;

/**
 * This is the model class for table "alerts".
 *
 * @property int $id
 * @property int $userId
 * @property string $name
 * @property int $status
 * @property int $condicion
 * @property int $createdAt
 * @property int $updatedAt
 * @property int $createdBy
 * @property int $updatedBy
 *
 * @property Users $user
 * @property Keywords[] $keywords
 */
class Alerts extends \yii\db\ActiveRecord
{
    public $files;
    public $free_words = [];
    public $dictionaryIds = [];
    public $productsIds;
    public $alertResourceId  = [];

    const STATUS_ACTIVE    = 1;
    const STATUS_INACTIVE  = 0;
    
    
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'alerts';
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
            [['userId','name','alertResourceId','productsIds'], 'required', 'on' => 'saveOrUpdate'],
            [['status'], 'default','value' => 1],
            [['userId', 'status', 'createdAt', 'updatedAt', 'createdBy', 'updatedBy'], 'integer'],
            [['name'], 'string', 'max' => 255],
            [['userId'], 'exist', 'skipOnError' => true, 'targetClass' => Users::className(), 'targetAttribute' => ['userId' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'        => Yii::t('app', 'ID'),
            'userId'    => Yii::t('app', 'User ID'),
            'name'      => Yii::t('app', 'Nombre'),
            'status'    => Yii::t('app', 'Status'),
            'alertResourceId'=> Yii::t('app', 'Recursos Sociales'),
            'dictionaryIds'=> Yii::t('app', 'Diccionarios'),
            'productsIds'=> Yii::t('app', 'Productos'),
            'createdAt' => Yii::t('app', 'Created At'),
            'updatedAt' => Yii::t('app', 'Updated At'),
            'createdBy' => Yii::t('app', 'Created By'),
            'updatedBy' => Yii::t('app', 'Updated By'),
        ];
    }


    /**
     * [getBringAllAlertsToRun get all the alerts with resources,products only use to console actions]
     * @return [array] [if not alerts with condition return a empty array]
     */
    public function getBringAllAlertsToRun($read = false,$resourceName = ''){

        // get time
        $timestamp = \app\helpers\DateHelper::getToday();
        // get all alert with relation config with the condicion start_date less or equals to $timestamp
        $alerts = $this->find()->where([
            'status' => self::STATUS_ACTIVE,
        ])->with(['config' => function($query) use($timestamp,$read,$resourceName) {
            $query->andWhere([
                'and',
                    ['<=', 'start_date', $timestamp],
                ]);
            if ($read) {
                if($resourceName != ''){
                    $query->with(['configSources.alertResource' => function($query) use ($resourceName){
                         $alisResource = [
                             'Twitter' => 'Twitter',
                             'LiveChat' => 'Live Chat',
                             'LiveConversation' => 'Live Chat Conversations',
                             'PaginasWebs' => 'Paginas Webs',
                             'FacebookComments' => 'Facebook Comments',
                             'InstagramComments' => 'Instagram Comments',
                             'FacebookMessages' => 'Facebook Messages',
                             'ExcelDocument' => 'Excel Document'
                        ];
                         $query->andWhere(['name' =>$alisResource[$resourceName]]);
                    }]);
                    
                }else{
                    $query->with(['configSources.alertResource' => function($query) use ($resourceName){
                        $query->andWhere([
                        'and',
                            ['!=', 'name', 'Paginas Webs'],
                        ]);
                    }]);
                }
            }else{
                $query->with(['configSources.alertResource']);
            }
        }
        ])->orderBy('id DESC')->asArray()->all();
        
        $alertsConfig = null;
        // there is alert in the model
        if(!empty($alerts)){
            // reorder data
            $alertsConfig = \app\helpers\AlertMentionsHelper::orderConfigSources($alerts);
            if ($read) {
                $alertsConfig = \app\helpers\AlertMentionsHelper::checksSourcesCall($alertsConfig);
            }
            //get family/products/models
            $alertsConfig = \app\helpers\AlertMentionsHelper::setProductsSearch($alertsConfig);
            
        }
       return $alertsConfig;
    }

    /**
     * [getBringAllAlertsToFinish get all the alerts to finish]
     * @return [array] [if not alerts with condition return a empty array]
     */
    public function getBringAllAlertsToFinish(){
        // get time
        $timestamp = \app\helpers\DateHelper::getToday();
        // get all alert with relation config with the condicion start_date less or equals to $timestamp
        $alerts = $this->find()->where([
            'status' => self::STATUS_ACTIVE,
        ])->with(['config' => function($query) use($timestamp) {
            $query->andWhere([
                'and',
                    ['<=', 'end_date', $timestamp],
                   // ['>=', 'end_date', $timestamp],
                ]);
            $query->with(['configSources.alertResource']);
        }
        ])->orderBy('id DESC')->asArray()->all();

        $alertsConfig = [];
        if(!empty($alerts)){
            // loop searching alert with mentions relation and config relation
            for($a = 0; $a < sizeOf($alerts); $a++){
                if((!empty($alerts[$a]['config']))){
                    // reduce configSources.alertResource
                    for($s = 0; $s < sizeOf($alerts[$a]['config']['configSources']); $s ++){
                        $alertResource = ArrayHelper::getValue($alerts[$a]['config']['configSources'][$s], 'alertResource');
                        $alerts[$a]['config']['configSources'][$s] = $alertResource;
                    } // end for $alerts[$a]['config']['configSources']
                    array_push($alertsConfig, $alerts[$a]);
                } // end if not empty
            } // end loop alerts config

        }

        return $alertsConfig;
    }

    /**
     * [getDictionaries get dictionaries for form]
     * @return [array] []
     */
    public function getDictionaries(){
        $dictionaries = Dictionaries::getDb()->cache(function ($db) {
            return Dictionaries::find()->all();
        },60);
        $dictionariesIds = \yii\helpers\ArrayHelper::map($dictionaries,'id','name');
        return $dictionariesIds;
    }

    
    public function getSocial(){
        $socials = Resources::getDb()->cache(function ($db) {
            return Resources::find()->where(['resourcesId' => [1,2,3]])->all();
        },60);
        $socialIds = \yii\helpers\ArrayHelper::map($socials,'id','name');
        return $socialIds;
    }

    /**
     * return free_words dictionaries words [form]
     * @return array
     */
    public function getFreeKeywords()
    {
        $words = [];
        $dictionary = Dictionaries::find()->where(['name' => Dictionaries::FREE_WORDS_NAME])->one();
        if(!is_null($dictionary)){
            $keywords =  $this->getKeywords()->where(['dictionaryId' => $dictionary->id])->orderBy('id')->all();
             
            if($keywords){
                foreach ($keywords as $keyword){
                    $words[] = $keyword->name;
                }
            }

        }
        
        return $words;     
    }
   
    /**
     * return  dictionaries name [form]
     * @return array
     */
    public function getDictionariesName(){
        $rows = (new \yii\db\Query())
        ->select('dictionaries.name')
        ->from('dictionaries')
        ->join('JOIN', 'keywords', 'keywords.dictionaryId = dictionaries.id')
        ->where(['keywords.alertId' => $this->id])
        ->groupBy('name')
        ->all();

        $dictionaryNames = [];

        for($r = 0; $r < sizeOf($rows); $r++){
            $dictionaryNames[$rows[$r]['name']] = $rows[$r]['name'];

        }

        return $dictionaryNames;
    }
    /**
     * return  products/models name [form]
     * @return array
     */
    public function getProducts(){

        $productsIds = ProductsModelsAlerts::find()->where(['alertId' => $this->id])->all();
        $product_models = [];

        foreach ($productsIds as $product) {
            $product_models[$product->productModel->id] = $product->productModel->name;
        }

        return $product_models;


    }

    public function getTermsFind(){
       // $alertsMentions = AlertsMencions::find()->with('mentions')->where(['alertId' => $this->id])->all();
        $alertsMentions = (new \yii\db\Query())
        ->cache(50)
        ->from('alerts_mencions')
        ->join('JOIN', 'mentions', 'mentions.alert_mentionId = alerts_mencions.id')
        ->where(['alertId' => $this->id]);
       
        
        $product_models = [];
        foreach($alertsMentions->each() as $alertMentions => $alertMention){
            $term_searched = $alertMention['term_searched'];
            $product_models[$term_searched] = $term_searched;
        }
        return $product_models;
    }


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getConfig()
    {
        return $this->hasOne(AlertConfig::className(), ['alertId' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(Users::className(), ['id' => 'userId']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getKeywords()
    {
        //return $this->hasMany(Keywords::className(), ['alertId' => 'id']);
        return $this->hasMany(Keywords::className(), ['id' => 'keywordId'])
            ->viaTable('alerts_keywords', ['alertId' => 'id']);
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAlertsKeywords(){
        return $this->hasMany(AlertsKeywords::className(), ['alertId' => 'id']); 
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDictionariesIdsByAlert()
    {
        // set resources id select2
        $selectIds = [];
        $exclude = ['Free Words','Product Competition'];
        foreach ($this->alertsKeywords as $alertkeyword) {
          if(!in_array($alertkeyword->keyword->dictionary->name,$selectIds) && !in_array($alertkeyword->keyword->dictionary->name,$exclude)){
            $selectIds[] =  $alertkeyword->keyword->dictionary->id;
          }  
          
        }   
        return $selectIds;                  
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAlertsMentions()
    {
        return $this->hasMany(AlertsMencions::className(), ['alertId' => 'id']);
    }

}
