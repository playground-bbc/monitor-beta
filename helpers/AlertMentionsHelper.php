<?php
namespace app\helpers;

use yii;

/**
 *
 * @author Eduardo Morales <eduardo@montana-studio.com>
 * @group  Montana-Studio LG 
 */

/**
 * AlertMentionsHelper wrapper for table db function.
 *
 */
class AlertMentionsHelper
{
    /**
     * [saveAlertsMencions save in alerts_mencions model]
     * @param  array  $properties [description]
     * @return [type]             [description]
     */
    public static function saveAlertsMencions($where = [], $properties = []){

        $is_model = \app\models\AlertsMencions::find()->where($where)->one();
        // if there a record 
        if($is_model){
            $model = \app\models\AlertsMencions::find()->where($where)->one();
            foreach($properties as $property => $value){
                $model->$property = $value;
            }
        }

        // if not there a record
        if(is_null($is_model)){
            $model = new  \app\models\AlertsMencions();

            foreach($where as $property => $value){
                $model->$property = $value;
            }

            foreach($properties as $property => $value){
                $model->$property = $value;
            }
        }
        return ($model->save()) ? $model : false;

    }
    /**
     * [getAlersMentions get the alerts previus mentions call]
     * @return [obj / null] [the objects db query]
     */
    public static function getAlersMentions($properties = []){
        $alertsMencions = \app\models\AlertsMencions::find()->where($properties)->asArray()->all();

        return (!empty($alertsMencions)) ? $alertsMencions : null;
    }

    /**
     * [isAlertsMencionsExists if a mention alert exits]
     * @param  [type]  $publication_id [description]
     * @return boolean                 [description]
     */
    public static function isAlertsMencionsExists($publication_id){
        if(\app\models\AlertsMencions::find()->where( [ 'publication_id' => $publication_id] )->exists()){
            return true;
        }
        return false;
    }


    /**
     * [getSocialNetworkInteractions return array of social with interation]
     * @param  [type] $resource_name [description]
     * @param  [type] $resource_id   [description]
     * @param  [type] $alertId       [description]
     * @return [type]                [description]
     */
    public static function getSocialNetworkInteractions($resource_name,$resource_id,$alertId)
    {
        $data = [];

        switch ($resource_name) {
            
            case 'Facebook Comments':
                $model = new \app\models\AlertsMencions();
                $model->alertId = $alertId;
                $model->resourcesId = $resource_id;
                
                return array($resource_name,$model->shareFaceBookPost,'0',$model->likesFacebookComments,$model->total);
                break;

            case 'Facebook Messages':
                $model = new \app\models\AlertsMencions();
                $model->alertId = $alertId;
                $model->resourcesId = $resource_id;
                
                return array($resource_name,'0','0','0',$model->total);
                break;    

            case 'Instagram Comments':
                $model = new \app\models\AlertsMencions();
                $model->alertId = $alertId;
                $model->resourcesId = $resource_id;
                
                return array($resource_name,'0',$model->likesInstagramPost,$model->likesFacebookComments,$model->total);
                break;
            case 'Twitter':
                $model = new \app\models\AlertsMencions();
                $model->alertId = $alertId;
                $model->resourcesId = $resource_id;

                return array($resource_name,$model->twitterRetweets,'0',$model->twitterLikes,$model->twitterTotal);
            
                break;
            case 'Live Chat':
                $model = new \app\models\AlertsMencions();
                $model->alertId = $alertId;
                $model->resourcesId = $resource_id;

                return array($resource_name,'0','0','0',$model->total);

                break;

            case 'Live Chat Conversations':
                $model = new \app\models\AlertsMencions();
                $model->alertId = $alertId;
                $model->resourcesId = $resource_id;

                return array($resource_name,'0','0','0',$model->total);

                break;                

            
            default:
                # code...
                return  null;
                break;
        }
    }
	
    public static function getPostInteractions($resource_name,$resource_id,$alertId)
    {
        $data = [];
        switch ($resource_name) {
            case 'Facebook Comments':
                $model = new \app\models\AlertsMencions();
                $model->alertId = $alertId;
                $model->resourcesId = $resource_id;
                
                return $model->topPostFacebookInterations;
                break;
            case 'Instagram Comments':
                $model = new \app\models\AlertsMencions();
                $model->alertId = $alertId;
                $model->resourcesId = $resource_id;
                
                return $model->topPostInstagramInterations;
                break;    
            
            default:
                # code...
                break;
        }
    }

}