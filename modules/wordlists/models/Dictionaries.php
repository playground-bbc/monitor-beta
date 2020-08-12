<?php

namespace app\modules\wordlists\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "dictionaries".
 *
 * @property int $id
 * @property string $name
 * @property string $color
 * @property int $createdAt
 * @property int $updatedAt
 * @property int $createdBy
 * @property int $updatedBy
 *
 * @property Keywords[] $keywords
 */
class Dictionaries extends \yii\db\ActiveRecord
{

    const FREE_WORDS_NAME  = 'Free Words';
    const FREE_WORDS_PRODUCT  = 'Product description';
    const FREE_WORDS_COMPETITION = 'Product Competition';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'dictionaries';
    }
    /**
     * {@inheritdoc}
     */
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
            [
              'class' => BlameableBehavior::className(),
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
            [['name', 'color'], 'required'],
            [['createdAt', 'updatedAt', 'createdBy', 'updatedBy'], 'integer'],
            [['name', 'color'], 'string', 'max' => 45],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'color' => 'Color',
            'createdAt' => 'Created At',
            'updatedAt' => 'Updated At',
            'createdBy' => 'Created By',
            'updatedBy' => 'Updated By',
        ];
    }
    /**
     * saveDictionary: save keywords dictionaries on table alerts_keywords for relations with alert
     * @param array $dictionaryIds [ids for dictionaries]
     * @param integer $alertId [id for alert ]
     */
    public static function saveDictionary($dictionaryIds, $alertId){
        if($dictionaryIds){
           $keywordsIds = \app\modules\wordlists\models\Keywords::find()->select('id')->where(['dictionaryId' => $dictionaryIds])->asArray()->all(); 
           $ids = \yii\helpers\ArrayHelper::getColumn($keywordsIds, 'id');
            if(!empty($ids)){
              $models = [];
              for ($i=0; $i < sizeOf($ids) ; $i++) { 
                $models[] = [$alertId,$ids[$i]];
              }
              if(count($models)){
                Yii::$app->db->createCommand()->batchInsert('alerts_keywords', ['alertId','keywordId'],$models)
                ->execute();
              }
            }
        }
    }
     /**
     * updateDictionaries: update keywords dictionaries on table alerts_keywords for relations with alert
     * @param array $dictionariesIds [ids for dictionaries]
     * @param integer $alertId [id for alert ]
     */
    public static function updateDictionaries($dictionariesIds,$alertId){

      if(!empty($dictionariesIds)){
        $keywordsIds = \app\modules\wordlists\models\Keywords::find()->select('id')->where(['dictionaryId' => $dictionariesIds])->all();
        $ids = \yii\helpers\ArrayHelper::getColumn($keywordsIds, 'id');
        // delete olds
        \app\modules\wordlists\models\AlertsKeywords::deleteAll([
          'alertId' => $alertId,
        ]);

        self::saveDictionary($dictionariesIds,$alertId);
        
      }else{
        // delete olds
        \app\modules\wordlists\models\AlertsKeywords::deleteAll([
          'alertId' => $alertId,
        ]);
      }
    }
    /**
     * saveFreeWords: save keywords in the dictionarie free_keywords and save his relation wih alert
     * @param array $dictionaryIds [ids for dictionaries]
     * @param integer $alertId [id for alert ]
     */
    public static function saveFreeWords($free_words,$alertId,$dictionaryName){
        $models = [];
        $dictionary =\app\modules\wordlists\models\Dictionaries::find()->where(['name' => $dictionaryName])->one();

        foreach ($free_words as $word){
          $keyword = new \app\modules\wordlists\models\Keywords();
          $keyword->name = $word;
          $keyword->dictionaryId = $dictionary->id;
          if($keyword->save()){
            $models[] = [$alertId,$keyword->id];
          }
          
        }
        if(count($models)){
          // save free words 
          Yii::$app->db->createCommand()->batchInsert('alerts_keywords', ['alertId','keywordId'],$models)
            ->execute();
        }
    }
  
    /**
     * saveOrUpdateWords: save keywords in the dictionarie free_keywords and save his relation wih alert
     * @param array $free_words [names of free words text box in the form]
     * @param integer $alertId [id for alert ]
     * @param integer $dictionaryId [id for dictionary free word ]
     */
    public static function saveOrUpdateWords($free_words,$alertId,$dictionaryId){

      $alertKeywords = \app\modules\wordlists\models\AlertsKeywords::find()->where(['alertId' => $alertId])->all();

      $words_delete = [];
      
      foreach ($alertKeywords as $alertKeyword) {
          if(!in_array($alertKeyword->keyword->name,$free_words)){
              $words_delete[] = $alertKeyword->keyword->id;
          }
      }
      $models = [];
      for($w = 0; $w < count($free_words); $w++){
          $isExists = \app\modules\wordlists\models\Keywords::find()->where(['dictionaryId' => $dictionaryId,'name' => $free_words[$w] ])->exists();
          if(!$isExists){
              $model = new \app\modules\wordlists\models\Keywords();
              $model->dictionaryId = $dictionaryId;
              $model->name = $free_words[$w];
              $model->save();
          }else{
            $model = \app\modules\wordlists\models\Keywords::find()->where(['dictionaryId' => $dictionaryId,'name' => $free_words[$w] ])->one();
          }
          $models[] = [$alertId,$model->id];

      }

      if(count($models)){
        Yii::$app->db->createCommand()->batchInsert('alerts_keywords', ['alertId','keywordId'],$models)
        ->execute();
      }

      if(count($words_delete)){
        \app\modules\wordlists\models\AlertsKeywords::deleteAll([
          'id' => $words_delete,
          'alertId' => $alertId,
        ]);

        \app\modules\wordlists\models\Keywords::deleteAll([
          'id' => $words_delete,
          'dictionaryId' => $dictionaryId,
        ]);
      }

    }

    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getKeywords()
    {
        return $this->hasMany(Keywords::className(), ['dictionaryId' => 'id']);
    }
}
