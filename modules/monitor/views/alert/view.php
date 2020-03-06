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
<link rel="stylesheet" href="https://d1azc1qln24ryf.cloudfront.net/114779/Socicon/style-cf.css?u8vidh">
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
        'attributes' => [
            [
                'label' => Yii::t('app','Estado'),
                'format'    => 'raw',
                'attribute' => 'status',
                'value' => function($model) {
                    return ($model->status) ? 'Active' : 'Inactive';
                }
            ],
            /*[
                'label' => Yii::t('app','Usuario'),
                'attribute' => 'userId',
                'format' => 'raw',
                'value' => function($model){
                    return $model->user->username;
                }
            ],*/
            [
                'label' => Yii::t('app','Nombre de la Alerta'),
                'attribute' => 'name',
                'format' => 'raw',
                'value' => function($model) {
                  return $model->name;
                }
            ],
            
            [
                'label' => Yii::t('app','Recursos Sociales'),
                'format'    => 'raw',
                'attribute' => 'alertResourceId',
                'value' => function($model) {
                    $html = '';
                    foreach ($model->config->configSources as $alert) {
                        $html .= "<span class='label label-info'>{$alert->alertResource->name}</span><status-alert id={$alert->alertResource->id} :resourceids={$alert->alertResource->id}></status-alert>";
                    }
                    return $html;
                },

            ],
            [
                'label' => Yii::t('app','Terminos a Buscar'),
                'format'    => 'raw',
                //'attribute' => 'alertResourceId',
                'value' => Select2::widget([
                    'name' => 'products',
                    'size' => Select2::SMALL,
                    'hideSearch' => false,
                    'data' => $model->products,
                    'options' => ['placeholder' => 'Terminos...'],
                    'pluginOptions' => [
                        'allowClear' => true
                    ],
                ]),

            ],
            
            'config.start_date:datetime',
            'config.end_date:datetime',
        ],
    ]) ?>


    <modal-alert></modal-alert>
    
    <div v-if="isData">
        <div class="row">
            <total-mentions :count="count" :shares="shares" :likes="likes" :coments="coments" :likes_comments="likes_comments">
        </div>
        <div class="">
          <box-sources></box-sources>
       </div>
       <div class="row">
            <div class="col-md-12">
                <total-resources-chart>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <post-interation-chart>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <products-interations-chart>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <count-date-resources-chart>
            </div>
        </div>
        <div id="mentions-list" class="row">
            <list-mentions></list-mentions>
        </div>
        <div class="row">
            <cloud-words></cloud-words>
        </div>
        
        <!-- <div class="row">
            <list-emojis></list-emojis>
        </div> -->
            
        
        <!-- <div class="row">
            <resource-date-mentions></resource-date-mentions>
        </div> -->

        
    </div>
    <div v-else>
        <div class="loader">
          <div class="spinner" style="height: 15vh;width:  15vh;"></div>
        </div>
    </div>
 
     

</div>

<?= $this->render('_templates-vue');  ?>




