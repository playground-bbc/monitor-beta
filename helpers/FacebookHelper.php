<?php
namespace app\helpers;

use yii;
use yii\helpers\Url;

use app\models\CredencialsApi;


/**
 *
 * @author Eduardo Morales <eduardo@montana-studio.com>
 * @group  Montana-Studio LG 
 */

/**
 * FacebookHelper wrapper for file function facebook.
 *
 */
class FacebookHelper
{

	private static $_resource_id = 5;

	/**
     * return facebook object.
     * @return facebook object
     */
	public static function getFacebook(){

		$fb = new \Facebook\Facebook([
		  'app_id' => Yii::$app->params['facebook']['app_id'],
		  'app_secret' => Yii::$app->params['facebook']['app_secret'],
		  'default_graph_version' => 'v3.2',
		  // 'default_access_token' => '' // https://developers.facebook.com/docs/facebook-login/access-tokens#apptokens
		]);

		return $fb;
	}
	/**
     * return login link for facebook.
     * @return string link
     */
	public static function loginLink(){
		$fb = self::getFacebook();
		$helper = $fb->getRedirectLoginHelper();
		// Optional permissions
		$permissions = [
			'manage_pages',
			'read_insights',
			'ads_management',
			'instagram_basic',
			'pages_show_list',
			'pages_messaging',
			'read_page_mailboxes',
			'instagram_manage_insights'
		]; 
		// crea una URL absoluta: http://www.example.com/index.php?r=post/index
		$url = Yii::$app->urlManager->createAbsoluteUrl(['monitor/facebook/validate-fb']);

		$loginUrl = $helper->getLoginUrl($url, $permissions);

		return htmlspecialchars($loginUrl);
	}
	/**
     * return logout link.
     * @param string $access_secret_token
     * @param string $next
     * @return string link
     */
	public static function logoutLink($user_facebook){
		
		$fb = self::getFacebook();
		$helper = $fb->getRedirectLoginHelper();

		// crea una URL absoluta: http://www.example.com/index.php?r=post/index
		$next = Yii::$app->urlManager->createAbsoluteUrl(['default/site/index']);
		//$next = 'http://localhost/monitor-beta/web/site/index';

		$logoutUrl = $helper->getLogoutUrl($user_facebook->access_secret_token, $next);
		return htmlspecialchars($logoutUrl);
	}
	/**
     * save access token.
     * @param int $userId
     * @param string $access_secret_token
     * @return boolean 
     */
	public static function saveAccessToken($userId,$access_secret_token){

		$is_model = CredencialsApi::find() ->where( [ 
						'userId' => $userId, 
						'resourceId' => self::$_resource_id,
						'name_app' => Yii::$app->params['facebook']['name_app'],
						 ] )->exists(); 

		if($is_model){
			$userCredential = CredencialsApi::find() ->where( [ 
				'userId' => $userId, 
				'resourceId' => self::$_resource_id,
				'name_app' => Yii::$app->params['facebook']['name_app']
			] )->one();
			
			$userCredential->userId = $userId;
			$userCredential->resourceId = self::$_resource_id;
			$userCredential->access_secret_token = $access_secret_token;
		}

		if(!$is_model){
			$userCredential = new CredencialsApi();
			$userCredential->name_app = Yii::$app->params['facebook']['name_app'];
			$userCredential->userId = $userId;
			$userCredential->resourceId = self::$_resource_id;
			$userCredential->access_secret_token = $access_secret_token;
		}
		
		
		return ($userCredential->save());
	}
	/**
     * save access token.
     * @param int $userId
     * @param string $expiresAt
     * @return boolean 
     */
	public static function saveExpiresAt($userId,$expiresAt){
		$userCredential = CredencialsApi::find() ->where( [ 
				'userId' => $userId, 
				'resourceId' => self::$_resource_id,
				'name_app' => Yii::$app->params['facebook']['name_app'],
			] )->one();

		$userCredential->expiration_date = $expiresAt;
		$userCredential->status = 1;
		
		return ($userCredential->save());
	}
	/**
     * if long live acces secret .
     * @param int $userId
     * @return boolean 
     */
	public static function isLongLived($userId){
		$userCredential = CredencialsApi::find() ->where( [ 
				'userId' => $userId, 
				'resourceId' => self::$_resource_id,
				'name_app' => Yii::$app->params['facebook']['name_app'],
			] )->one();
		if (isset($userCredential->expiration_date)) {
            return $userCredential->expiration_date > time() + (60 * 60 * 2);
        }
        return null;
	}
	/**
     * if is Expired acces secret .
     * @param int $userId
     * @return boolean 
     */
	public static function isExpired($userId){

		$userCredential = CredencialsApi::find() ->where( [ 
				'userId' => $userId, 
				'resourceId' => self::$_resource_id,
				'name_app' => Yii::$app->params['facebook']['name_app'],
			] )->one();
		if (isset($userCredential->expiration_date)) {
            return $userCredential->expiration_date < time();
        }
        return null;
	}
	/**
	 * [isCaseUsage true is over limit the usage api facebook or Instagram]
	 * @param  [int]                $header_business [headers api call]
	 * @return boolean              [true is over limit]
	 */
	public static function isCaseUsage($header_business,$business_id = ""){


        $headers_decode = json_decode($header_business,true);

        $business_id = (empty($business_id)) ? Yii::$app->params['facebook']['business_id'] : $business_id;
        $call_count = (int) $headers_decode[$business_id][0]['call_count'];

        if($call_count > 85){
        	return true;
        }
        $total_cputime = (int) $headers_decode[$business_id][0]['total_cputime'];


        if($total_cputime > 85){
        	return true;
        }

        return false;

	}
	/**
	 * [isPublicationNew determine if publication is new with respect to last publication]
	 * @param  [type]  $unix_last_date [description]
	 * @param  [type]  $unix_new_date  [description]
	 * @return boolean                 [description]
	 */
	public static function isPublicationNew($unix_last_date,$unix_new_date){

		$diffForHumans = explode(" ",\app\helpers\DateHelper::diffForHumans($unix_last_date,$unix_new_date));
		
		$adverb = end($diffForHumans);

		if($adverb == 'before'){
			return true;
		}
		return false;
	}

	/**
	 * [getCredencials return object credencial by userId]
	 * @param  [int] $userId [id from user table]
	 * @return [object / null]         [description]
	 */
	public static function getCredencials($userId){

		$userCredential = CredencialsApi::find() ->where( [ 
				'userId' => $userId, 
				'resourceId' => self::$_resource_id,
				'name_app' => Yii::$app->params['facebook']['name_app'],
			] )->one();

		return ($userCredential) ? $userCredential : null;
			

	}

	/**
	 * [getAppsecretProof return app secret proof]
	 * @param  [string] $access_token [access_token from credencialApi table]
	 * @return [string]               [AppsecretProof]
	 */
	public static function getAppsecretProof($access_token)
	{
		$app_secret = \Yii::$app->params['facebook']['app_secret'];
		return hash_hmac('sha256', $access_token, $app_secret); 
	}



}