<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

$action = (isset($view)) ? $view : 'view';

?>

<div class="">
    <?php 
    $form = ActiveForm::begin(
        [
          'id' => 'mentions-search',
          'action' => [$action],
          'method' => 'get',
          'options' => [
              'data-pjax' => 1,
              'style' => 'display: none;'
           
            ],
        ]);
    ?>
    
    <?= Html::hiddenInput('id','') ?>
    
    <?= Html::hiddenInput('resourceId','') ?>

    <?= $form->field($model, 'resourceName') ?>

    <?= $form->field($model, 'termSearch') ?>

    <?= $form->field($model, 'name') ?>

    <?= $form->field($model, 'screen_name') ?>

    <?= $form->field($model, 'subject') ?>

    <?= $form->field($model, 'message_markup') ?>


    <div class="form-group">
        <?= Html::submitButton('Search', ['id' => 'search']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
