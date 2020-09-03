<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model app\models\LoginForm */

use yii\helpers\Html;
use yii\web\View;
use yii\bootstrap\ActiveForm;

$this->title = 'Actualizar Usuario';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="text-center" style ="margin-top:80px">
    <h1><?= Html::encode($this->title) ?></h1>

    <p>Please fill out the following fields to login:</p>

    <?php $form = ActiveForm::begin([
        'id' => 'edit-user-form',
        'layout' => 'horizontal',
    ]); ?>

        <?= $form->field($model, 'username')->textInput(['autofocus' => true]) ?>

        <?= $form->field($model, 'email') ?>
        
        <?= $form->field($model, 'password')->passwordInput(['placeholder' => '************']) ?>

        <?= $form->field($model, 'password_repeat')->passwordInput(['placeholder' => '************']) ?>

        <div class="form-group">
            <div class="">
                <?= Html::submitButton('Actualizar', ['class' => 'btn btn-primary btn-md', 'name' => 'login-button']) ?>
            </div>
        </div>

    <?php ActiveForm::end(); ?>

    <div class="" style="color:#999;">
    </div>
</div>
