<?php

use yii\helpers\Html;
//use yii\widgets\DetailView;
use macgyer\yii2materializecss\widgets\data\DetailView;
use kartik\select2\Select2;


\app\assets\SweetAlertAsset::register($this);
\app\assets\AxiosAsset::register($this);
\app\assets\VueAsset::register($this);
\app\assets\DataTableAsset::register($this);
\app\assets\JqcloudAsset::register($this);
\app\assets\highchartsAsset::register($this);
\app\assets\GoogleChartAsset::register($this);
\app\assets\ViewAsset::register($this);

/* @var $this yii\web\View */
/* @var $model app\models\Alerts */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Alerts', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$input = "<input type='text' v-model='test' value='".$model->id."'>";

\yii\web\YiiAsset::register($this);
?>
<div id="alerts-view" class="alerts-view" style="padding-top: 10px">
    <h1><?= Html::encode($this->title) ?></h1>
    <?= Html::hiddenInput('alertId', $model->id,['id' => 'alertId']); ?>

    <p>
      

        <?= Html::a('Actualizar', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Eliminar', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
        <button-report :count="count">
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => \app\helpers\AlertMentionsHelper::getAttributesForDetailView($model)
    ]) ?>

    <modal-alert :count="count"></modal-alert>
    
    <div v-if="isData">
        <div class="row">
            <total-mentions :count="count" :resourcescount="resourcescount">
        </div>
       <div class="row">
            <div class="col-md-12">
            
                <total-resources-chart :is_change="is_change" >
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <post-interation-chart :is_change="is_change">
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <products-interations-chart :is_change="is_change"> 
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <count-date-resources-chart :is_change="is_change">
            </div>
        </div>
        <div id="mentions-list" class="row">
            <list-mentions :is_change="is_change">
        </div>
        <div class="row">
            <cloud-words :is_change="is_change">
        </div>
    </div>
    <div v-else>
        <div class="loader">
          <div class="spinner" style="height: 15vh;width:  15vh;"></div>
        </div>
    </div>
 
     

</div>

<?= $this->render('_templates-vue',
    [
        'dataProvider' => $dataProvider,
        'searchModel' => $searchModel,
    ]);  
?>




