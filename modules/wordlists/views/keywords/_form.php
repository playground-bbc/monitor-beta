<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;

/* @var $this yii\web\View */
/* @var $model app\models\Keywords */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="keywords-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'dictionaryId')->widget(Select2::classname(), [
                    'data' => ArrayHelper::map(\app\modules\wordlists\models\Dictionaries::find()->asArray()->all(), 'id','name'),
                    'options' => [
                        'disabled' => true
                        
                    ],
                ]);
        ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
