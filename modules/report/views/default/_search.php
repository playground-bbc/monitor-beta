<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\modules\presentation\models\PresentationSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="presentation-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
        'options' => [
            'data-pjax' => 1
        ],
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'userId') ?>

    <?= $form->field($model, 'name') ?>

    <?= $form->field($model, 'head_title') ?>

    <?= $form->field($model, 'title') ?>

    <?php // echo $form->field($model, 'date') ?>

    <?php // echo $form->field($model, 'url_sheet') ?>

    <?php // echo $form->field($model, 'url_presentation') ?>

    <?php // echo $form->field($model, 'status') ?>

    <?php // echo $form->field($model, 'updated') ?>

    <?php // echo $form->field($model, 'createdAt') ?>

    <?php // echo $form->field($model, 'updatedAt') ?>

    <?php // echo $form->field($model, 'createdBy') ?>

    <?php // echo $form->field($model, 'updatedBy') ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Search'), ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton(Yii::t('app', 'Reset'), ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
