<?php
/* @var $this yii\web\View */
use yii\helpers\Html;
use yii\widgets\DetailView;

\app\assets\AxiosAsset::register($this);
\app\assets\VueAsset::register($this);
\app\assets\JqcloudAsset::register($this);
\app\assets\highmapsAsset::register($this);
\app\assets\DetailAsset::register($this);

$this->title = $resource->name;
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['/monitor/alert/view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = $resource->name;

$name = (isset(\Yii::$app->params['resourcesName'][$resource->name])) ? \Yii::$app->params['resourcesName'][$resource->name] : $resource->name;
?>
<div id="alerts-detail" class="alerts-detail" style="padding-top: 10px">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
            <?= Html::tag('h1', Html::encode($name)) ?>
            <?= Html::hiddenInput('resourceName', $resource->name,['class' => 'resourceName']); ?>
            <?= Html::hiddenInput('alertId', $model->id,['id' => 'alertId']); ?>

            <p>
                <?= Html::a('Actualizar', ['/monitor/alert/update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
                <?= Html::a('Eliminar', ['/monitor/alert/delete', 'id' => $model->id], [
                    'class' => 'btn btn-danger',
                    'data' => [
                        'confirm' => 'Are you sure you want to delete this item?',
                        'method' => 'post',
                    ],
                ]) ?>
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
            :alertid= <?= $model->id ?> 
            :resourceid= <?= $resource->id ?>
            >
        </div>
    </div>
</div>

<?= $this->render('_templates-vue',[
    'searchModel' => $searchModel,
    'dataProvider' => $dataProvider,
    'view' => 'index',
    'resource' => $resource
]);  
?>