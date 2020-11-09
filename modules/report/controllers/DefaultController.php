<?php

namespace app\modules\report\controllers;

use yii;
use yii\web\Controller;

/**
 * Default controller for the `report` module
 */
class DefaultController extends Controller
{
    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionIndex()
    {
        $client = new \Google_Client();
        $client->setApplicationName('report-lg-montana-studio');
        $scopes = \app\modules\report\helpers\PresentationHelper::getScope();
        $client->setScopes($scopes);
        $pathCredentials = \Yii::getAlias('@app/modules/report/credentials/credentials.json');
        $client->setAuthConfig($pathCredentials);
        $client->setAccessType('offline');
        $client->setPrompt('select_account consent');

        // Load previously authorized token from a file, if it exists.
        // The file token.json stores the user's access and refresh tokens, and is
        // created automatically when the authorization flow completes for the first
        // time.
        $userId = \Yii::$app->user->id;
        $tokenPath = \Yii::getAlias("@app/modules/report/credentials/{$userId}.json");;
        if (file_exists($tokenPath)) {
            $accessToken = json_decode(file_get_contents($tokenPath), true);
            $client->setAccessToken($accessToken);
        }
        
        if(Yii::$app->request->get('code')){

            $authCode = Yii::$app->request->get('code');
            // Exchange authorization code for an access token.
            $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);
            $client->setAccessToken($accessToken);
            // Save the token to a file.
            if (!file_exists(dirname($tokenPath))) {
                mkdir(dirname($tokenPath), 0700, true);
            }
            file_put_contents($tokenPath, json_encode($client->getAccessToken()));
            return $this->render('index');
            // Check to see if there was an error.
            if (array_key_exists('error', $accessToken)) {
                throw new Exception(join(', ', $accessToken));
            }
        }

        // If there is no previous token or it's expired.
        if ($client->isAccessTokenExpired()) {
            // Refresh the token if possible, else fetch a new one.
            if ($client->getRefreshToken()) {
                $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
            } else {
                // Request authorization from the user.
                $authUrl = $client->createAuthUrl();
                return $this->redirect($authUrl);
                
            }
            
        }
        return $this->render('index');
    }
}
