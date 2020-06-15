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

            <p>
                <?= Html::a('Regresar', ['alert/view', 'id' => $model->id], ['class' => 'btn btn-default']) ?>
            </p>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
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
                        [
                            'label' => Yii::t('app','Recurso'),
                            'format'    => 'raw',
                            'value' => function($model) use($resource) {
                                return Html::encode($resource->name);
                            }
                        ],
                        [
                            'label' => Yii::t('app','Terminos a Buscar'),
                            'format'    => 'raw',
                            'value' => \kartik\select2\Select2::widget([
                                'name' => 'products',
                                'size' => \kartik\select2\Select2::SMALL,
                                'hideSearch' => false,
                                'data' => $model->termsFind,
                                'options' => ['placeholder' => 'Terminos...'],
                                'pluginOptions' => [
                                    'allowClear' => true
                                ],
                            ]),

                        ],
                    ]
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

<?= $this->render('_templates-vue');  
?>