<?php

namespace app\modules\products\controllers;

use Yii;
use app\models\ProductsSeries;
use app\modules\products\models\ProductsSeriesSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * ProductsSeriesController implements the CRUD actions for ProductsSeries model.
 */
class ProductsSeriesController extends Controller
{
    private $itemId = 0;
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
     * Lists all ProductsSeries models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new ProductsSeriesSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single ProductsSeries model.
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
     * Creates a new ProductsSeries model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new ProductsSeries();

        if ($model->load(Yii::$app->request->post())) {
            if ($model->save()) {
                Yii::$app->session->setFlash('success', "Success created successfully: {$model->name}");
            } else {
                Yii::$app->session->setFlash('error', "Error not saved.");
            }
            if(Yii::$app->request->post('redirect')){
                $model = new ProductsSeries();
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
     * Updates an existing ProductsSeries model.
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
                $model = new ProductsSeries();
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
     * Deletes an existing ProductsSeries model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        // delete relation with insights table
        \app\models\WProductsFamilyContent::deleteAll([
            'serieId' => $model->id
        ]);
        $model->delete();

        return $this->redirect(['/products/default','itemId' => $this->itemId]);
    }

    /**
     * Finds the ProductsSeries model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return ProductsSeries the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = ProductsSeries::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
    }
}
