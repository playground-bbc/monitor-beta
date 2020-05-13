<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

use kartik\date\DatePicker;
use kartik\select2\Select2;

/* @var $this yii\web\View */
/* @var $model app\modules\topic\models\MTopics */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="mtopics-form">

    <?php $form = ActiveForm::begin(); ?>
    <div>
        <?= $form->field($model, 'userId')->hiddenInput(['value'=> \Yii::$app->user->getId()])->label(false); ?>
    </div>
    <div class="row">
        <div class="col-md-6">
            <?= $form->field($model, 'name')->textInput(['maxlength' => true,'placeholder' => 'Ej: En tiempos de COVID']) ?>
        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'end_date')->widget(DatePicker::classname(), [
                    'type' => DatePicker::TYPE_INPUT,
                    'options' => ['placeholder' => 'Ingrese Fecha Final'],
                    'pluginOptions' => [
                        'format' => 'yyyy-mm-dd',
                       // 'startView' => 1,
                        'todayHighlight' => true,
                        'autoclose' => true,
                        'startDate'=>"0d"
                    ],
                    'pluginEvents' => [
                    ],
                ]); 
            ?>
        </div>     
    </div>

    <div class="row">
        <div id="resourceId_row" class="">
            <?= $form->field($model, 'resourceId')->widget(Select2::classname(), [
                    'data' => \yii\helpers\ArrayHelper::map(app\modules\topic\models\MResources::find()->all(),'id','name'),
                    'options' => [
                        'id' => 'topic_resourcesId',
                        'placeholder' => 'Selecione la red Social',
                        'multiple' => true,
                        'theme' => 'krajee',
                        'debug' => false,
                        'value' => ($model->resourcesIds) ? $model->resourcesIds : [] ,
                       
                    ],
                    'pluginEvents' => [
                    ],
                    'toggleAllSettings' => [
                       'selectLabel' => '',
                       'unselectLabel' => '',
                       'selectOptions' => ['class' => 'text-success'],
                       'unselectOptions' => ['class' => 'text-danger'],
                    ],
                ]);
            ?>
        </div>
        <div id="locationId_row" class="">
            <?= $form->field($model, 'locationId')->widget(Select2::classname(), [
                    'data' => \yii\helpers\ArrayHelper::map(app\modules\topic\models\MLocations::find()->all(),'id','name'),
                    'options' => [
                        'id' => 'locationId',
                        'placeholder' => 'Selecciona el Pais', 
                        'value' => ($model->locations) ? $model->locations: [], 
                    ],
                    'pluginOptions' => [
                    ],
                ]); 
            ?> 
        </div>
        <div id="dictionaryId_row" class="">
            <?= $form->field($model, 'dictionaryId')->widget(Select2::classname(), [
                    'data' => $drive->dictionariesTitlesForTopic,
                    'options' => [
                        'placeholder' => 'Selecione los Diccionarios',
                        'multiple' => true,
                        'theme' => 'krajee',
                        'debug' => false,
                        'value' => ($model->dictionaries) ? $model->dictionaries : [],
                       
                    ],
                    'pluginEvents' => [
                    ],
                    'toggleAllSettings' => [
                    ],
                ]);
            ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <?= $form->field($model, 'urls')->widget(Select2::classname(), [
                'options' => [
                    'id' => 'topic_urls',
                    'placeholder' => 'Ingrese url a Buscar', 
                    'multiple' => true,
                ],
                'pluginOptions' => [
                    'tags' => true,
                    'tokenSeparators' => [',', ' '],
                    'minimumInputLength' => 2
                ],
            ]); 
            ?>  
        </div>
    </div>
    

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
<?php 

$this->registerJsFile(
    '@web/js/topic/form.js',
    ['depends' => [
        \app\assets\VueAsset::className(),
        \app\assets\SweetAlertAsset::className(),
        ]
    ]
);

?>