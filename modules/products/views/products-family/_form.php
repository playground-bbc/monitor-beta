<?php

use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;

/* @var $this yii\web\View */
/* @var $model app\models\ProductsFamily */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="products-family-form">

    <?php $form = ActiveForm::begin(); ?>
    <div class="row">
        <div class="col-md-6">
            <?= $form->field($model, 'seriesId')->widget(Select2::classname(), [
                    'data' => ArrayHelper::map(\app\models\ProductsSeries::find()->asArray()->all(), 'id','name'),
                    'options' => [
                        'placeholder' => 'Seleciona una Categoria',
                        'multiple' => false,
                        'theme' => 'krajee',
                        'debug' => false,
                        // 'value' => (isset($alert->config->configSourcesByAlertResource)) 
                        //             ? $alert->config->configSourcesByAlertResource : [],
                        
                    ],
                ]);
            ?>
        </div>
        <div class="col-md-6">
             <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>
        </div>
    </div>
    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Guardar'), ['class' => 'btn btn-success']) ?>
        <?= Html::submitButton(Yii::t('app', 'Guardar y Crear nuevo registro'), ['class' => 'btn btn-info','name' =>'redirect','value' => 'false']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
