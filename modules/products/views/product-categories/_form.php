<?php

use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;

/* @var $this yii\web\View */
/* @var $model app\models\ProductCategories */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="product-categories-form">

    <?php $form = ActiveForm::begin(); ?>
    <div class="row">
        <div class="col-md-6">
        <?= $form->field($model, 'products_familyId')->widget(Select2::classname(), [
                    'data' => ArrayHelper::map(\app\models\ProductsFamily::find()->asArray()->all(), 'id','name'),
                    'options' => [
                        'placeholder' => 'Seleciona una Sub Categoria',
                        'multiple' => false,
                        'theme' => 'krajee',
                        'debug' => false,
                        
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
