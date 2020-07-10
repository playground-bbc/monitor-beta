<?php

namespace app\modules\products\controllers;

use Yii;
use yii\web\Controller;

use app\models\ProductsSeries;
use app\modules\products\models\ProductsSeriesSearch;

use app\models\ProductsFamily;
use app\modules\products\models\ProductsFamilySearch;

use app\models\ProductCategories;
use app\modules\products\models\ProductCategoriesSearch;

use app\models\Products;
use app\modules\products\models\ProductsSearch;

use app\models\ProductsModels;
use app\modules\products\models\ProductsModelsSearch;

/**
 * Default controller for the `products` module
 */
class DefaultController extends Controller
{
    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionIndex()
    {
        $productSeriesSearchModel = new ProductsSeriesSearch();
        $productSeriesDataProvider = $productSeriesSearchModel->search(Yii::$app->request->queryParams);

        $productFamilySearchModel = new ProductsFamilySearch();
        $productFamilyDataProvider = $productFamilySearchModel->search(Yii::$app->request->queryParams);

        $ProductCategoriesSearchModel = new ProductCategoriesSearch();
        $ProductCategoriesDataProvider = $ProductCategoriesSearchModel->search(Yii::$app->request->queryParams);

        $productSearchModel = new ProductsSearch();
        $productDataProvider = $productSearchModel->search(Yii::$app->request->queryParams);

        $productModelsearchModel = new ProductsModelsSearch();
        $productModeldataProvider = $productModelsearchModel->search(Yii::$app->request->queryParams);


        return $this->render('index', [
            'productSeriesSearchModel' => $productSeriesSearchModel,
            'productSeriesDataProvider' => $productSeriesDataProvider,

            'productFamilySearchModel' => $productFamilySearchModel,
            'productFamilyDataProvider' => $productFamilyDataProvider,

            'ProductCategoriesSearchModel' => $ProductCategoriesSearchModel,
            'ProductCategoriesDataProvider' => $ProductCategoriesDataProvider,

            'productSearchModel' => $productSearchModel,
            'productDataProvider' => $productDataProvider,

            'productModelsearchModel' => $productModelsearchModel,
            'productModeldataProvider' => $productModeldataProvider,
        ]);
    }

    public function actionCheckProducts($value){
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $tablesNames = [
            \app\models\ProductsSeries::tableName(),
            \app\models\ProductsFamily::tableName(),
            \app\models\ProductCategories::tableName(),
            \app\models\Products::tableName(),
            \app\models\ProductsModels::tableName(),
        ];
        $expression = new \yii\db\Expression('LOWER(`name`) = LOWER("'.$value.'")');

        for ($t=0; $t < sizeOf($tablesNames) ; $t++) { 
            $rows = (new \yii\db\Query())
            ->cache(5)
            ->select(['id','name'])
            ->from($tablesNames[$t])
            ->where($expression)
            ->all();
            
            if(count($rows)){
               $rows['tableName'] = $tablesNames[$t];
               break;
            }
        }

        
        return (count($rows)) ? $rows : '';
    }
    
}
