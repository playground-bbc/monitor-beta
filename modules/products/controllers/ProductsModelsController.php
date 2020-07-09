<?php

namespace app\modules\products\controllers;

use Yii;
use app\models\ProductsModels;
use app\modules\products\models\ProductsModelsSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * ProductsModelsController implements the CRUD actions for ProductsModels model.
 */
class ProductsModelsController extends Controller
{
    private $itemId = 4;
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all ProductsModels models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new ProductsModelsSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single ProductsModels model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new ProductsModels model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new ProductsModels();

        if ($model->load(Yii::$app->request->post())) {
            if ($model->save()) {
                Yii::$app->session->setFlash('success', "Success created successfully: {$model->name}");
            } else {
                Yii::$app->session->setFlash('error', "Error not saved.");
            }
            if(Yii::$app->request->post('redirect')){
                $model = new ProductsModels();
                return $this->render('create', [
                    'model' => $model,
                ]);
            }
            return $this->redirect(['/products/default','itemId' => $this->itemId]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing ProductsModels model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post())) {
            if ($model->save()) {
                Yii::$app->session->setFlash('success', "Success update successfully: {$model->name}");
            } else {
                Yii::$app->session->setFlash('error', "Error not saved.");
            }
            if(Yii::$app->request->post('redirect')){
                $model = new ProductsModels();
                return $this->render('create', [
                    'model' => $model,
                ]);
            }
            return $this->redirect(['/products/default','itemId' => $this->itemId]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing ProductsModels model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['/products/default','itemId' => $this->itemId]);
    }

    /**
     * Finds the ProductsModels model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return ProductsModels the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = ProductsModels::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
    }
}
