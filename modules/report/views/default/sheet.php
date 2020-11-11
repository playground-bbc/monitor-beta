<?php 
use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = Yii::t('app', 'Create Presentation');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Presentations'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;


?> 
<div class="presentation-presentation-sheet">
    <div class="row">
        <div class="col-md-2"></div>
        <div class="col-md-8">
            <?php $form = ActiveForm::begin(['id' => 'sheet-form']); ?>
            <div class="panel panel-default">
                <div class="panel-body">
                    <div class="row">
                        <div class="col-md-12">
                            <?= $form->field($sheetForm, 'url') ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <?= Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-success']) ?>
                    </div>
                </div>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
        <div class="col-md-2"></div>
    </div>
</div>
