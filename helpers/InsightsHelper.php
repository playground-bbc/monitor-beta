<?php
namespace app\helpers;

use yii;
use yii\helpers\Url;


/**
 * 
 */
class InsightsHelper
{
	
	/**
     * [saveMencions save in alerts_mencions model]
     * @param  array  $properties [description]
     * @return [type]             [description]
     */
    public static function saveContent($where = [], $properties = []){
       
        $is_model = \app\models\WContent::find()->where($where)->one();
        // if there a record 
        if($is_model){
            $model = \app\models\WContent::find()->where($where)->one();
        }

        // if not there a record
        if(is_null($is_model)){
            $model = new  \app\models\WContent();

            foreach($where as $property => $value){
                $model->$property = $value;
            }

            foreach($properties as $property => $value){
                $model->$property = $value;
            }
            // save or update
        	if(!$model->save()){
        		var_dump($model->errors);
        	}
        }
        return $model;

    }


    public static function saveInsights($insights,$contentId)
    {
    	$model = $insights;
		for ($m=0; $m < sizeof($model) ; $m++) { 
			$insights = new \app\models\WInsights();
			$insights->content_id = $contentId;
			$insights->name = $model[$m]['name'];
			$insights->title = $model[$m]['title'];
			$insights->description = $model[$m]['description'];
			$insights->insights_id = $model[$m]['id'];
			$insights->period = $model[$m]['period'];
			for ($v=0; $v < sizeof($model[$m]['values']) ; $v++) {
				if (!is_array($model[$m]['values'][$v]['value'])) {
					$insights->value = $model[$m]['values'][$v]['value'];
					$insights->end_time = \app\helpers\DateHelper::getToday();
				}else{
					foreach ($model[$m]['values'][$v]['value'] as $key => $value) {
						$property = '_'.$key; 
						$insights->$property = $value;
					}
				}
			}

			if(!$insights->save()){
				var_dump($insights->errors);
			}
		}
    }


    public static function saveAttachments($attachments,$contentId)
    {
    	if (!empty($attachments)) {
    		for ($a=0; $a < sizeof($attachments) ; $a++) { 
    			$is_attachment = \app\models\WAttachments::find()->where(
    				[
    					'title' => $attachments[$a]['title'],
    					'content_id' => $contentId,
    					'type' => $attachments[$a]['media_type'],
    				]
    			)->one();

    			if (is_null($is_attachment)) {
    				$model = new \app\models\WAttachments();

    				if ($attachments[$a]['media_type'] != 'album') {
    					$model->content_id = $contentId;
    					$model->title = $attachments[$a]['title'];
    					$model->type = $attachments[$a]['media_type'];
    					$model->src_url = $attachments[$a]['media']['image']['src'];
    					$model->save();
    				}
    			}
    		}
    	}
    }
}