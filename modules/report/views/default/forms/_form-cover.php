<?php 
use kartik\date\DatePicker;
?>
<div class="panel panel-primary">
    <div class="panel-heading">Slide Portada #1</div>
    <div class="panel-body">
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <?= $form->field($model, 'head_title')->textInput(['maxlength' => true]) ?>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <?= $form->field($model, 'title')->textInput(['maxlength' => true]) ?>
                </div>
            </div>
            
        </div>
    </div>
</div>