<?php 
namespace app\helpers;

use yii;
use app\models\HistorySearch;
use app\models\Alerts;


/**
 * HistorySearchHelper wrapper for table db function.
 * @author Eduardo Morales <eduardo@montana-studio.com>
 * @group  Montana-Studio LG 
 */
 class HistorySearchHelper
 {
    /**
     * create or update History Model
     * @param int $alertId id alert
     * @param array $properties
     * @return void
     */
    public static function createOrUpdate($alertId,$properties)
    {
      $is_model = HistorySearch::find()->where(['alertId' => $alertId])->exists();
      $model = Alerts::findOne($alertId);

      $resourceIds = $model->config->configSourcesByAlertResource;

      if($is_model){
        $model = HistorySearch::find()->where(['alertId' => $alertId])->one();
        
        $key = array_keys($properties);
        $resource_search = $model->search_data;
        // update resource
        foreach ($resource_search as $resource => $value) {
          if(!in_array($value['resourceId'], $resourceIds)){
              unset($resource_search[$resource]); 
          }
        } 

        
        if(\yii\helpers\ArrayHelper::keyExists($key[0],$resource_search)){
          $resource_search[$key[0]]['status'] =  $properties[$key[0]]['status'];
          $model->search_data = $resource_search;

        }else{
          $model->search_data = \yii\helpers\ArrayHelper::merge($resource_search, $properties);
        }
          
        $model->save();
          
      }else{
          $model = new HistorySearch();
          $model->alertId = $alertId;
          $model->search_data = $properties;
          // save or update
          $model->save();
      }

    }

    /**
     * delete History Model
     * @param int $alertId id alert
     * @return void
     */
    public static function deleteHistory($alertId)
    {
      $model = \app\models\Alerts::findOne($alertId);
      if ($model){
        $history = \app\models\HistorySearch::findOne(['alertId' => $alertId]);
        if ($history) {
          $history->delete();
        }
      }
    }

    /**
     * status record history search
     * @param int $alertId id alert
     * @return boolean
     */
    public static function checkResourceByStatus($alertId,$resource,$status)
    {
      $model = \app\models\Alerts::findOne($alertId);
      if ($model) {
        $history = \app\models\HistorySearch::findOne(['alertId' => $alertId]);
        if ($history) {
          $search_data = $history->search_data;
          if (isset($search_data[$resource]['status'])) {
            $resource_status = $search_data[$resource]['status'];
            return ($status == $resource_status) ? true : false; 
          }
        }
      }
      return false;
    }
     
 }

?>