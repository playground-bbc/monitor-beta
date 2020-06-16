<?php

namespace app\modules\monitor\controllers;

use yii\web\NotFoundHttpException;

class DetailController extends \yii\web\Controller
{
    public function actionIndex($id,$resourceId)
    {
        $model = $this->findModel($id,$resourceId);
        $resource = \app\models\Resources::findOne($resourceId);
        
        $searchModel = new \app\models\grid\MentionSearch();
        $dataProvider = $searchModel->search(\Yii::$app->request->queryParams,$id,$resource->id);
        
        return $this->render('index',[
            'model' => $model,
            'resource' => $resource,
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }




    public function actionView()
    {
        return $this->render('view');
    }

    /**
     * Finds the Alerts model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Alerts the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id,$resourceId)
    {
        if (($model = \app\models\Alerts::findOne($id)) !== null) {
            $alertResources = \yii\helpers\ArrayHelper::map($model->config->sources,'id','name');
            if(in_array($resourceId,array_keys($alertResources))){
                return $model;
            }else{
                throw new NotFoundHttpException('The resource page does not exist for this Alert.');  
            }
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

}
