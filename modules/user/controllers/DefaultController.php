<?php

namespace app\modules\user\controllers;

use yii;
use yii\web\Controller;

/**
 * Default controller for the `user` module
 */
class DefaultController extends Controller
{
    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }
    /**
     * Renders the edit view for the module
     * @return string
     */
    public function actionEdit()
    {
        $model =  new \app\modules\user\models\UserEditForm();
        $user  = \app\models\Users::findOne(\Yii::$app->user->getId());

        $model->username = $user->username;
        $model->email = $user->email;
        $model->password_repeat;
        $model->password;

        if(Yii::$app->request->post() && $model->load(Yii::$app->request->post())){
            $user->username = $model->username;
            $user->email = $model->email;
            if(!empty($model->password) && ($model->password === $model->password_repeat)){
                $hash = Yii::$app->security->generatePasswordHash($model->password);
                $user->password_hash = $hash;
            }
            if($model->validate()){
                $user->save();
                Yii::$app->session->setFlash('success', "Usuario Actualizado.");
                return $this->redirect(['//monitor/alert']);
            }else{
                Yii::$app->session->setFlash('error', "Problemas al Actualizar el Usuario.");
            }
            
        }

        return $this->render('edit',['model' => $model]);
    }
}
