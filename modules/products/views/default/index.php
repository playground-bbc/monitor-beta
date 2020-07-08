<?php
use kartik\tabs\TabsX;
$items = [
    [
        'label'=>'<i class="fas fa-home"></i> Categorias',
        'content'=> \Yii::$app->controller->renderPartial('/products-series/index',
        [
            'searchModel' => $productSeriesSearchModel,
            'dataProvider' => $productSeriesDataProvider,
        ]),
        'active'=>true
    ],
    [
        'label'=>'<i class="fas fa-user"></i> Sub Categorias',
        'content'=> \Yii::$app->controller->renderPartial('/products-family/index',
        [
            'searchModel' => $productFamilySearchModel,
            'dataProvider' => $productFamilyDataProvider,
        ]),
    ],
    [
        'label'=>'<i class="fas fa-user"></i> Modelos',
        'content'=> \Yii::$app->controller->renderPartial('/product-categories/index',
        [
            'searchModel' => $ProductCategoriesSearchModel,
            'dataProvider' => $ProductCategoriesDataProvider,
        ]),
    ],
    [
        'label'=>'<i class="fas fa-user"></i> Productos',
        'content'=> \Yii::$app->controller->renderPartial('/products/index',
        [
            'searchModel' => $productSearchModel,
            'dataProvider' => $productDataProvider,
        ]),
        //'linkOptions'=>['data-url'=>\yii\helpers\Url::to(['/site/tabs-data'])]
    ],
    [
        'label'=>'<i class="fas fa-user"></i> Codigos',
        'content'=> \Yii::$app->controller->renderPartial('/products-models/index',
        [
            'searchModel' => $productModelsearchModel,
            'dataProvider' => $productModeldataProvider,
        ]),
        //'linkOptions'=>['data-url'=>\yii\helpers\Url::to(['/site/tabs-data'])]
    ],
   
];
$this->title = Yii::t('app', 'Products');
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="products-default-index">
    <h1><?= $this->context->action->uniqueId ?></h1>
    <?php
        // Left
        echo TabsX::widget([
            'items'=>$items,
            'position'=>TabsX::POS_LEFT,
            'encodeLabels'=>false
        ]);

        
    ?>

</div>
