<?php
namespace app\widgets\insights;

use Yii;
use yii\helpers\Html;

/**
 * Alert widget renders a message from session flash. All flash messages are displayed
 * in the sequence they were assigned using setFlash. You can set message as following:
 *
 * ```php
 * Yii::$app->session->setFlash('error', 'This is the message');
 * Yii::$app->session->setFlash('success', 'This is the message');
 * Yii::$app->session->setFlash('info', 'This is the message');
 * ```
 *
 * Multiple messages could be set as follows:
 *
 * ```php
 * Yii::$app->session->setFlash('error', ['Error 1', 'Error 2']);
 * ```
 *
 */
class InsightsWidget extends \yii\bootstrap\Widget
{
	
    public $userId;
    public $userCredencial = [];


    public function init()
    {
        parent::init();
        $this->userId = Yii::$app->user->id;
        $this->userCredencial = \app\helpers\FacebookHelper::getCredencials($this->userId);
        
    }

	public function run()
	{
        $link = \app\helpers\FacebookHelper::loginLink();
        $url_link = "<a href='{$link}'>Log in with Facebook!</a>";

        if (!\Yii::$app->user->isGuest) {
            if (is_null($this->userCredencial)) {
                $message = Yii::t('app','Por favor Inicie sesión con facebook: '.$url_link);
                $class   = 'alert-info';
                return $this->render('alert',['message' => $message,'class' => $class]);
            }else{

                if (!$this->userCredencial->status) {
                    $message = Yii::t('app','Por favor Inicie sesión con facebook: '.$url_link);
                    $class   = 'alert-warning';
                    return $this->render('alert',['message' => $message,'class' => $class]);
                }

                $is_expired = \app\helpers\FacebookHelper::isExpired($this->userId);
                if ($is_expired) {
                    $message = Yii::t('app','Su sesión de facebook ha caducado: '.$url_link);
                    $class   = 'alert-warning';
                    return $this->render('alert',['message' => $message,'class' => $class]);

                }
            }
        }
        


        return $this->render('dashboard');
	}
}