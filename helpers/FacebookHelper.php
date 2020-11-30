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
	private static $_baseUrl = 'https://graph.facebook.com/v4.0';

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
		if (!is_null($userCredential)) {
			if (isset($userCredential->expiration_date)) {
	            return $userCredential->expiration_date < time();
	        }
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
        $call_count = (isset($headers_decode[$business_id][0])) ? (int) $headers_decode[$business_id][0]['call_count'] :  0;

        if($call_count > 85){
        	return true;
        }
        $total_cputime = (isset($headers_decode[$business_id][0])) ? (int) $headers_decode[$business_id][0]['total_cputime'] : 0;


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



	/**
	 * [_getPageAccessToken get page access token token]
	 * @param  [string] $access_secret_token [description]
	 * @return [string] [PageAccessToken]
	 */
	public static function getPageAccessToken($user_credential){
		
		$appsecret_proof = \app\helpers\FacebookHelper::getAppsecretProof($user_credential->access_secret_token);
		$params = [
            'access_token' => $user_credential->access_secret_token,
            'appsecret_proof' => $appsecret_proof
        ];

        $page_access_token = null;


        $client = new yii\httpclient\Client(['baseUrl' => self::$_baseUrl]);
       
        try{
        	
        	$accounts = $client->get('me/accounts',$params)->send();
        	$data = $accounts->getData();
        	if(isset($data['error'])){
        		// to $user_credential->user->username and $user_credential->name_app
        		// error send email with $data['error']['message']
        		return null;
        	}
        	$page_access_token = yii\helpers\ArrayHelper::getColumn($data['data'],'access_token')[0]; 

        }catch(\yii\httpclient\Exception $e){
        	// problem conections
        	// send a email
        }
        

        return (!is_null($page_access_token)) ? $page_access_token : null;
	}

	/**
	 * [_getBusinessAccountId get bussinessId]
	 * @param  [type] $user_credential [description]
	 * @return [string]                  [description]
	 */
	public static function getBusinessAccountId($access_secret_token){
		
		$bussinessId = Yii::$app->params['facebook']['business_id'];
		$appsecret_proof = \app\helpers\FacebookHelper::getAppsecretProof($access_secret_token);

		$params = [
            'access_token'    => $access_secret_token,
            'appsecret_proof' => $appsecret_proof
        ];

        $BusinessAccountId = null;
        $client = new yii\httpclient\Client(['baseUrl' => self::$_baseUrl]);
       
        try{
        	
        	$accounts = $client->get("{$bussinessId}?fields=instagram_business_account",$params)->send();
        	$data = $accounts->getData();
        	if(isset($data['error'])){
        		// to $user_credential->user->username and $user_credential->name_app
        		// error send email with $data['error']['message']
        		return null;
        	}
      
        	$BusinessAccountId = $data['instagram_business_account']['id']; 

        }catch(\yii\httpclient\Exception $e){
        	// problem conections
        	// send a email
        }
        

        return (!is_null($BusinessAccountId)) ? $BusinessAccountId : null;

	}


	/**
	 * [getUserActiveFacebook take a user active with credential facebook]
	 * @return [array] [users]
	 */
	public static function getUserActiveFacebook()
	{
		$usersFacebook = \app\models\Users::find()->select('id')->where([
            'status' => 10
        ])->with(['credencialsApis' => function ($query)
            {   
                $query->andWhere(['resourceId' => 5]);
                $query->andWhere(['not', ['access_secret_token' => null]]);
                $query->andWhere(['not', ['access_secret_token' => 'encrycpt here']]);
                $query->andWhere([
                'and',
                    ['>=', 'expiration_date', time()],
                ]);
                $query->orderBy(['updatedAt' => 'DESC']);
            }
		])->asArray()->all();
		

        $usersFacebook = array_filter($usersFacebook,function ($user)
        {
            return (!empty($user['credencialsApis']));
        });

        // restores index
        $usersFacebook = array_values($usersFacebook);
        // get client
        $client = new yii\httpclient\Client(['baseUrl' => self::$_baseUrl]);

        for ($u=0; $u < sizeof($usersFacebook) ; $u++) { 

        	$access_secret_token = $usersFacebook[$u]['credencialsApis'][0]['access_secret_token'];

    		$appsecret_proof = self::getAppsecretProof($access_secret_token);
			$params = [
	            'access_token' => $access_secret_token,
	            'appsecret_proof' => $appsecret_proof
	        ];
        	
        	try {

        		$accounts = $client->get('me/accounts',$params)->send();

	        	$data = $accounts->getData();

	        	if(isset($data['error'])){
	        		// to $user_credential->user->username and $user_credential->name_app
	        		// error send email with $data['error']['message']
	        		return null;
	        	}else{
	        		$usersFacebook[$u]['credencial'] = $data;
	        		$usersFacebook[$u]['appsecret_proof'] = $appsecret_proof;
	        	}
	        	
        		
        	} catch (\yii\httpclient\Exception $e) {
        		echo $e->getMessage();
        	}
        }
        
        $userFacebook = self::getUserbyPermissions($usersFacebook);

       return $userFacebook;
	}

	/**
	 * [getUserbyPermissions return the user with the proper permission]
	 * @param  [array] $usersFacebook [users from db]
	 * @return [type]                [description]
	 */
	public static function getUserbyPermissions($usersFacebook)
	{
		$user = [];
		if (!empty($usersFacebook)) {
			for ($u=0; $u < sizeof($usersFacebook) ; $u++) { 
				if (!empty($usersFacebook[$u]['credencialsApis']) && !empty($usersFacebook[$u]['credencial'])) {
					for ($d=0; $d < sizeof($usersFacebook[$u]['credencial']['data']) ; $d++) { 
						//$name_app = $usersFacebook[$u]['credencial']['data'][$d]['name'];
						$taks = $usersFacebook[$u]['credencial']['data'][$d]['tasks'];
						if (in_array('MANAGE', $taks)) {
							$user['user_id'] = $usersFacebook[$u]['id']; 
							$user['credencial'] = $usersFacebook[$u]['credencial']['data'][0]; 
							$user['appsecret_proof'] = $usersFacebook[$u]['appsecret_proof']; 
						}
					}
				}
			}
		}

		return $user;
	}
	/**
	 * [getIdPostFacebook return id post]
	 * @param  [string] $id [string compuest by numerodelapagina_numerodepost]
	 * @return [string ]     [return numerodepost ]
	 */
	public static function getIdPostFacebook($id)
	{
		$post_id = explode('_', $id);
		return end($post_id);
	}

}