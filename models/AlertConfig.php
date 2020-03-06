<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

use app\helpers\DateHelper;

/**
 * This is the model class for table "alert_config".
 *
 * @property int $id
 * @property int $alertId
 * @property string $product_description
 * @property string $competitors
 * @property string $country
 * @property string $url_drive
 * @property int $start_date
 * @property int $end_date
 * @property int $createdAt
 * @property int $updatedAt
 * @property int $createdBy
 * @property int $updatedBy
 *
 * @property AlertconfigSources[] $alertconfigSources
 */
class AlertConfig extends \yii\db\ActiveRecord
{
    public $country = 'Chile';
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'alert_config';
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
              //  'value' => function() { return date('U');  },
            ],
        ];
    }
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['start_date','end_date'], 'required'],
            // normalize "start_date" and "end_date" using the function "normalizeDate"
            [['start_date','end_date'], 'filter', 'filter' => [$this, 'normalizeDate']],
            // normalize "phone" using the function "normalizeTags"
            [['product_description','competitors'], 'filter', 'filter' => [$this, 'normalizeTags']],
            // start_date not greater than end date
            // [['end_date'], 'validateDates'],
           // ['end_date', 'compare', 'compareAttribute'=> 'start_date', 'operator' => '>=', 'enableClientValidation' =>true],
            
            [['start_date','end_date'], 'date','format' => 'php:U'],
            
            [['alertId'], 'exist', 
              'skipOnError' => true, 
              'targetClass' => Alerts::className(), 
              'targetAttribute' => ['alertId' => 'id']]
        ];
    }

    public function beforeValidate()
    {
        if (!parent::beforeValidate()) {
            return false;
        }
        return true;
    }

    public function validateDates(){
        if(strtotime($this->end_date) <= strtotime($this->start_date)){
            $this->addError('end_date','Fecha Final no puede ser menor a fecha de Inicio');
        }
    }

    public function normalizeDate($value){

        return strtotime(str_replace('/','-',$value));

    }

    public function normalizeTags($value){
        return ($value) ? implode(",",$value) : '';
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'alertId' => Yii::t('app', 'Alerta ID'),
            'product_description' => Yii::t('app', 'Descripción del producto'),
            'competitors' => Yii::t('app', 'Competidores'),
            'country' => Yii::t('app', 'Url Drive'),
            'country' => Yii::t('app', 'País'),
            'start_date' => Yii::t('app', 'Fecha Inicio'),
            'end_date' => Yii::t('app', 'Fecha Final'),
            'createdAt' => Yii::t('app', 'Created At'),
            'updatedAt' => Yii::t('app', 'Updated At'),
            'createdBy' => Yii::t('app', 'Created By'),
            'updatedBy' => Yii::t('app', 'Updated By'),
        ];
    }

    public function saveAlertconfigSources($socialIds){
      if($socialIds){
        \app\models\AlertconfigSources::deleteAll('alertconfigId = '.$this->id);
        foreach($socialIds as $socialId){
            $model = new \app\models\AlertconfigSources();
            $model->alertconfigId = $this->id;
            $model->alertResourceId = $socialId;
            if(!$model->save()){
              return false;
            }
        }
      }
      return true;
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
    public function getConfigSources()
    {
        return $this->hasMany(AlertconfigSources::className(), ['alertconfigId' => 'id']);
    }

    public function getSources()
    {
        return $this->hasMany(Resources::className(), ['id' => 'alertResourceId'])
            ->viaTable('alertconfig_sources', ['alertconfigId' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getConfigSourcesByAlertResource()
    {
        $sources = $this->configSources;
        // set resources id select2
        $selectIds = [];
        foreach ($sources as $source) {
          $selectIds[] =  $source->alertResource->id;
        }   
        return $selectIds;                  
    }
}
