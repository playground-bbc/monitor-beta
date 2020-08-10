<?php

namespace app\modules\wordlists\models;

use Yii;

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

    public static function  saveDictionaryDrive($dictionaryIds, $alertId){
        if($dictionaryIds){
            $dictionaries = [];
            foreach ($dictionaryIds as $name) {
              $isDictionary = \app\models\Dictionaries::find()->where(['name' => $name])->exists();

              if(!$isDictionary){
                $color = substr(md5(time()), 0, 6);
                $dictionary = new \app\models\Dictionaries();
                $dictionary->name = $name;
                $dictionary->color = '#' . $color;
                $dictionary->save();
              }
              else{
                $dictionary = \app\models\Dictionaries::find()->where(['name' => $name])->one();
              }
               
              $dictionaries[$dictionary->id] = $dictionary->name;  
            } 
           
            if($dictionaries){
              $drive   = new \app\models\api\DriveApi();
              foreach ($dictionaries as $dictionaryId => $dictionaryName){
                $keywords_drive = $drive->getContentDictionaryByTitle([$dictionaryName]);
                foreach ($keywords_drive[$dictionaryName] as $word) {
                  if(!\app\models\Keywords::find()->where(['alertId' => $alertId,'dictionaryId'=> $dictionaryId,'name'=> $word])->exists()){
                    $models[] = [$alertId,$dictionaryId,$word];
                  }
                }
                  
              }
            
            }
            if(!empty($models)){
             // \app\models\Keywords::deleteAll('alertId = '.$alertId);
              Yii::$app->db->createCommand()->batchInsert('keywords', ['alertId','dictionaryId', 'name'],$models)
                        ->execute();
            }
        }
    }

    public static function saveFreeWords($free_words,$alertId,$dictionaryName){
      $model = [];
      $isFreeWords = \app\models\Dictionaries::find()->where(['name' => $dictionaryName])->exists();
      if(!$isFreeWords){
        $color = substr(md5(time()), 0, 6);
        $dictionary = new \app\models\Dictionaries();
        $dictionary->name = $dictionaryName;
        $dictionary->color = '#' . $color;
        $dictionary->save();
      }else{
        $dictionary = \app\models\Dictionaries::find()->where(['name' => $dictionaryName])->one();
      }

      foreach ($free_words as $word){
        $models[] = [$alertId,$dictionary->id,$word];
      }
      // save free words 
      Yii::$app->db->createCommand()->batchInsert('keywords', ['alertId','dictionaryId', 'name'],$models)
      ->execute();
    }


    public static function saveOrUpdateWords($free_words,$alertId,$dictionaryId){

      $words = \app\models\Keywords::find()->where(['alertId' => $alertId,'dictionaryId' => $dictionaryId ])->select(['name','id'])->all();

      $words_delete = [];

      foreach ($words as $word) {
          if(!in_array($word->name,$free_words)){
              $words_delete[] = $word->id;
          }
      }
      
      for($w = 0; $w < count($free_words); $w++){
          $isExists = \app\models\Keywords::find()->where(['alertId' => $alertId,'dictionaryId' => $dictionaryId,'name' => $free_words[$w] ])->exists();
          if(!$isExists){
              $model = new \app\models\Keywords();
              $model->alertId = $alertId;
              $model->dictionaryId = $dictionaryId;
              $model->name = $free_words[$w];
              $model->save();
          }

      }

      \app\models\Keywords::deleteAll([
        'id' => $words_delete,
        'alertId' => $alertId,
        'dictionaryId' => $dictionaryId
      ]);

    }

    public static function saveOrUpdateDictionaries($dictionariesNames,$alertId){

      $dictionaries = \app\models\Dictionaries::find()->select(['name','id'])->all();

      $dictionaries_delete = [];

      foreach ($dictionaries as $dictionarie) {
          if(!in_array($dictionarie->name,$dictionariesNames)){
              $dictionaries_delete[] = $dictionarie->id;
          }
      }
      
      self::saveDictionaryDrive($dictionariesNames,$alertId);

      \app\models\Keywords::deleteAll([
        'alertId' => $alertId,
        'dictionaryId' => $dictionaries_delete
      ]);

    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getKeywords()
    {
        return $this->hasMany(Keywords::className(), ['dictionaryId' => 'id']);
    }
}
