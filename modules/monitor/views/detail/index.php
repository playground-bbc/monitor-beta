<?php
/* @var $this yii\web\View */
use yii\helpers\Html;
use yii\widgets\DetailView;

\app\assets\AxiosAsset::register($this);
\app\assets\VueAsset::register($this);
\app\assets\JqcloudAsset::register($this);
\app\assets\DetailAsset::register($this);
?>
<div id="alerts-detail" class="alerts-detail" style="padding-top: 10px">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
            <h1><?= Html::encode($resource->name) ?></h1>
            <?= Html::hiddenInput('alertId', $model->id,['id' => 'alertId']); ?>

            <p>
                <?= Html::a('Regresar', ['alert/view', 'id' => $model->id], ['class' => 'btn btn-default']) ?>
            </p>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <?= DetailView::widget([
                    'model' => $model,
                    'attributes' => \app\helpers\DetailHelper::setGridDetailColumnsOnDetailView($model,$resource),
                ]) ?>
            </div>
        </div>
        <div class="row">
            <detail 
            :resourceid= <?= $resource->id ?>
            :alertid= <?= $model->id ?> 
            >
        </div>
    </div>
</div>

<?= $this->render('_templates-vue',[
    'searchModel' => $searchModel,
    'dataProvider' => $dataProvider,
    'view' => 'index'
]);  
?>