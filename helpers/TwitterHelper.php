<?php
namespace app\helpers;

use yii;
use Codebird\Codebird;

/**
 *
 * @author Eduardo Morales <eduardo@montana-studio.com>
 * @group  Montana-Studio LG 
 */

/**
 * TwitterHelper wrapper for logic Twitter.
 *
 */
class TwitterHelper
{
	/**
	 * [_getTwitterLogin login to twitter]
	 * @return [type] [description]
	 */
	public static function login($resourceId){

		$cb = new Codebird();
		$bearer_token = null;	
		$credencials_api = (new \yii\db\Query())
		    ->select('api_key,api_secret_key,bearer_token')
		    ->from('credencials_api')
		    ->where(['resourceId' => $resourceId])
		    ->all();
		if($credencials_api){
			$bearer_token = \yii\helpers\ArrayHelper::getColumn($credencials_api,'bearer_token')[0];
			if($bearer_token == ''){
				$api_key = \yii\helpers\ArrayHelper::getColumn($credencials_api,'api_key')[0];    
				$api_secret_key = \yii\helpers\ArrayHelper::getColumn($credencials_api,'api_secret_key')[0]; 
				Codebird::setConsumerKey($api_key, $api_secret_key);
				$reply = $cb::getInstance()->oauth2_token();
				if($reply->access_token){
					self::setBearerToken($reply->access_token,$resourceId);
				}
			}else{
				Codebird::setBearerToken($bearer_token);
				$cb = Codebird::getInstance();
			}
		}
		return $cb;    
	}
	/**
	 * [getBearerToken generate the bearer Token]
	 * @param  [type] $api_key        [description]
	 * @param  [type] $api_secret_key [description]
	 * @return [type]                 [description]
	 */
	public static function getBearerToken($api_key,$api_secret_key){
		Codebird::setConsumerKey($api_key, $api_secret_key); // static, see README
		$reply = Codebird::getInstance()->oauth2_token();
		$bearer_token = $reply->access_token;
		
		return $bearer_token;
	}


	/**
	 * [_setBearerToken set bearer_token in the database]
	 * @param [type] $bearer_token [description]
	 */
	public static function setBearerToken($bearer_token,$resourceId){
		
		// INSERT (table name, column values)
		Yii::$app->db->createCommand()->update('credencials_api', [
		    'bearer_token' => $bearer_token,
		],'resourceId ='.$resourceId)->execute();
	}


	public static function getLocationsForTopicId($topicId)
	{
		$topic = \app\modules\topic\models\MTopics::findOne($topicId);
		$locations = [];
		if (!is_null($topic)) {
			if (!is_null($topic->mTopicsLocations)) {
				foreach ($topic->mTopicsLocations as $topicLocation) {
					$locations[$topicLocation->location->id] = $topicLocation->location->woeid;
				}
			}
		}
		return $locations;
	}
}

?>