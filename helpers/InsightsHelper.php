<?php
namespace app\helpers;

use yii;
use yii\helpers\Url;


/**
 *  class helper to model Insights
 */
class InsightsHelper
{
	/**
     * [getData call to api with the end point]
     * @param  [string] $end_point [end point to call]
     * @param  [string] $params    [params in the call]
     * @return [array]            [data from the call or null]
     */
    public static function getData($end_point,$params)
    {
        $_baseUrl = 'https://graph.facebook.com/v6.0'; 

        $data = null;
        $client = new yii\httpclient\Client(['baseUrl' => $_baseUrl]);
        
        try {
            
            $response = $client->get($end_point,$params)->send();
            if ($response->isOk) {
                $data = $response->getData();

            }
            
            if(isset($data['error'])){
                // to $user_credential->user->username and $user_credential->name_app
                // error send email with $data['error']['message']
                $data = null;
            }
            
        } catch (\yii\httpclient\Exception $e) {
            // send email
        }
        return (!is_null($data))? $data : false;
    }
	/**
     * [saveMencions save in alerts_mencions model]
     * @param  array  $where      [conditions to find in the db]
     * @param  array  $properties [properties to save inst save]
     * @return [model]             [instance save in db]
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
    /**
     * [setReactionsFacebookPost deprecade]
     * @param [type] $insights [description]
     */
    public static function setReactionsFacebookPost($insights)
    {
        $reactions_key = [
            'like',
            'love',
            'wow',
            'haha',
            'sorry',
            'anger'
        ];

        $name = "post_reactions_by_type_";
        $title = "Lifetime Total post Reactions by ";
        $description = "Lifetime: Total post reactions by type.";


        $model = [];
        for ($i=0; $i < sizeof($insights) ; $i++) { 
            
            if ($insights[$i]['name'] == 'post_reactions_by_type_total' ) {
                if (!empty($insights[$i]['values'])) {
                    $values = reset($insights[$i]['values']);
                    foreach ($values as $value => $reactions) {
                        for ($r=0; $r <sizeof($reactions_key) ; $r++) { 
                            if (\yii\helpers\ArrayHelper::keyExists($reactions_key[$r],$reactions)) {
                                $reactions_name = $reactions_key[$r];
                                $val = $reactions[$reactions_name];
                                $model[] = \app\helpers\InsightsHelper::setMetric($name.$reactions_name,"lifetime",$val,$title.$reactions_name,$description,$insights[$i]['id']);
                            }else{
                                $reactions_name = $reactions_key[$r];
                                $val = 0;
                                $model[] = \app\helpers\InsightsHelper::setMetric($name.$reactions_name,"lifetime",$val,$title.$reactions_name,$description,$insights[$i]['id']); 
                            }
                        }
                       // echo $value."\n";
                    }
                    unset($insights[$i]);
                }
            }else{
              $model[] = $insights[$i];  
            }
        }

        return $model;

    }

    /**
     * [saveInsights save in Insights model]
     * @param  array  $where      [conditions to find in the db]
     * @param  array  $properties [properties to save inst save]
     */
    public static function saveInsightsFacebookPost($insights,$contentId)
    {
    	$model = $insights;
		for ($m=0; $m < sizeof($model) ; $m++) { 
			
            $where = [
                'content_id' => $contentId,
                'name' => $model[$m]['name'],
                'title' => $model[$m]['title'],
                'period' => $model[$m]['period'],
                'insights_id' => $model[$m]['id'],
                'description' => $model[$m]['description'],
                'end_time' => \app\helpers\DateHelper::getTodayDate(),
            ];

            $insight_exists = \app\models\WInsights::find()->where($where)->exists();
            if ($insight_exists) {
                $insights = \app\models\WInsights::find()->where($where)->one();
                
            }else{
                $insights = new \app\models\WInsights();
                $insights->end_time = \app\helpers\DateHelper::getTodayDate();
                foreach ($where as $property => $value) {
                    $insights->$property = $value;
                }
            }

            for ($v=0; $v < sizeof($model[$m]['values']) ; $v++) {
                if (!is_array($model[$m]['values'][$v]['value'])) {
                    $insights->value = $model[$m]['values'][$v]['value'];
                }else{
                    foreach ($model[$m]['values'][$v]['value'] as $key => $value) {
                        $property = "_{$key}";
                        $insights->$property = $value;
                    }
                }
            }

            if (!$insights->save()) {
                var_dump($model->errors());
            }

		}
    }

    /**
     * [saveAttachments save in Attachments model]
     * @param  array  $where      [conditions to find in the db]
     * @param  array  $properties [properties to save inst save]
     */
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
                        if(!$model->save()){
                            var_dump($model->errors);
                        }
    				}// end if media_type != album

                    if ($attachments[$a]['media_type'] == 'album') {
                        if (\yii\helpers\ArrayHelper::keyExists('subattachments',$attachments[$a])) {
                            if (!empty($attachments[$a]['subattachments']['data'])) {
                               $subattachments = $attachments[$a]['subattachments']['data'];

                               for ($s=0; $s < sizeof($subattachments); $s++) { 
                                    $model = new \app\models\WAttachments();
                                    $model->content_id = $contentId;
                                    $model->title = $attachments[$a]['title'];
                                    $model->type = $attachments[$a]['media_type'];
                                    $model->src_url = $subattachments[$s]['media']['image']['src'];
                                    if(!$model->save()){
                                        var_dump($model->errors);
                                    }
                               }// end loop 
                            }
                        }
                    } // end if media_type = album
    			}// end if is_null
    		} // end loop
    	}// end if empty
    }

    /**
     * [saveInsightsPage save insigth page in db]
     * @param  [type] $insights  [description]
     * @param  [type] $contentId [description]
     * @return [type]            [description]
     */
    public static function saveInsightsPage($insights,$contentId)
    {
        // ver si hay un insigth con fecha actual y si lo hay actualizar
        if (!empty($insights)) {
            for ($i=0; $i < sizeof($insights) ; $i++) { 
                $name        = $insights[$i]['name'];
                $period      = $insights[$i]['period'];
                $description = $insights[$i]['description'];
                $title        = $insights[$i]['title'];
                $insights_id = $insights[$i]['id'];
                if (!empty($insights[$i]['values'])) {
                    $values = $insights[$i]['values'];
                    for ($v=0; $v < sizeof($values) ; $v++) { 
                        if (\app\helpers\DateHelper::isToday($values[$v]['end_time'])) {
                            
                            $where = [
                                'content_id' => $contentId,
                                'name'       => $name,
                                'period'     => $period,
                                'description'=> $description,
                                'title'      => $title,
                                'insights_id'=> $insights_id,
                                'end_time'   => \app\helpers\DateHelper::asTimestamp($values[$v]['end_time']),
                            ];

                            $insight_exists = \app\models\WInsights::find()->where($where)->exists();
                            if ($insight_exists) {
                                $model = \app\models\WInsights::find()->where($where)->one();
                                $model->value = $values[$v]['value'];
                            }else{
                                $model = new \app\models\WInsights();
                                $model->content_id = $contentId;
                                $model->name = $name;
                                $model->period = $period;
                                $model->description = $description;
                                $model->title = $title;
                                $model->insights_id = $insights_id;
                                $model->value = $values[$v]['value'];
                                $model->end_time = \app\helpers\DateHelper::asTimestamp($values[$v]['end_time']);
                            }

                            if (!$model->save()) {
                                var_dump($model->errors);
                            }
                        }
                    }
                }
            }
        }// end if

    }

    /**
     * [saveInsightsInstagramPost description]
     * @param  [type] $insights  [description]
     * @param  [type] $contentId [description]
     * @return [type]            [description]
     */
    public static function saveInsightsInstagramPost($insights,$contentId)
    {
        $model = $insights;
        if (!empty($model)) {
            for ($m=0; $m < sizeof($model) ; $m++) { 

                $where = [
                    'name' => $model[$m]['name'],
                    'title' => $model[$m]['title'],
                    'description' => $model[$m]['description'],
                    'insights_id' => $model[$m]['id'],
                    'period' => $model[$m]['period'],
                    'content_id' => $contentId,
                    'end_time' => \app\helpers\DateHelper::getTodayDate(),
                ];
                
                $is_insights = \app\models\WInsights::find()->where($where)->exists();
                if ($is_insights) {
                    $insights = \app\models\WInsights::find()->where($where)->one();
                } else {
                    $insights = new \app\models\WInsights();
                    $insights->end_time = \app\helpers\DateHelper::getTodayDate();
                    foreach ($where as $property => $value) {
                        $insights->$property = $value;
                    }
                }

                if (!empty($model[$m]['values'])) {
                    $values = $model[$m]['values'];
                    for ($v=0; $v < sizeof($values) ; $v++) { 
                        $insights->value = $values[$v]['value'];
                    }   
                }
                
                if (!$insights->save()) {
                    var_dump($model->errors());
                }
                
            } 
        }
        
    }

    /**
     * [setMetric create array to new metric]
     * @param [type] $name        [description]
     * @param [type] $period      [description]
     * @param [type] $value       [description]
     * @param [type] $title       [description]
     * @param [type] $description [description]
     * @param string $id          [description]
     */
    public static function setMetric($name,$period,$value,$title,$description,$id = '')
    {
        return [
            'name' => $name,
            'period' => $period,
            'values' => [
                [
                    'value' => $value,
                    'end_time' => \app\helpers\DateHelper::getTodayDate(false)
                ]
            ],
            'title' => $title,
            'description' => $description,
            'id' => $id,

        ];
    }


}