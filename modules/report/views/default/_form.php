<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use wbraganca\dynamicform\DynamicFormWidget;
use kartik\typeahead\TypeaheadBasic;
/* @var $this yii\web\View */
/* @var $model app\modules\presentation\models\Presentation */
/* @var $form yii\widgets\ActiveForm */

$js = '
jQuery(".dynamicform_wrapper_dinamic").on("afterInsert", function(e, item) {
    jQuery(".dynamicform_wrapper_dinamic .panel-title-address").each(function(index) {
        jQuery(this).html("Slide Categorias:  " + (index + 1))
    });
});

jQuery(".dynamicform_wrapper_dinamic").on("afterDelete", function(e) {
    jQuery(".dynamicform_wrapper_dinamic .panel-title-address").each(function(index) {
        jQuery(this).html("Slide Categorias:  " + (index + 1))
    });
});
';

$this->registerJs($js);

?>

<div class="presentation-form">

    <?php $form = ActiveForm::begin(['id' => 'dynamic-form']); ?>
     <!-- error sumary  -->
     <?= $this->render('forms/_error-sumary',[
         'form' => $form,
         'model' => $model,
         'modelsSection' => $modelsSection,
         'modelsPage' => $modelsPage,
         'modelOverViewSection' => $modelOverViewSection,
         'modelOverViewPage' => $modelOverViewPage,
         'modelOverViewPageElement' => $modelOverViewPageElement,
         'sacForm' => $sacForm,
        ]) ?>
    <div class="panel panel-default">
        <div class="panel-body">
            <div class="row">
                <div class="col-md-12">
                    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>
                </div>
            </div>
             <!-- form cover Slide #1  -->
            <?= $this->render('forms/_form-cover',['form' => $form,'model' => $model]) ?>
            <!-- form multi Slides  -->
            <?= $this->render('forms/_form-section',['form' => $form,'modelsSection' => $modelsSection,'modelsPage' => $modelsPage]) ?>
            <!-- form multi Overview  -->
            <?= $this->render('forms/_form-overview',['form' => $form,'modelOverViewSection' => $modelOverViewSection,'modelOverViewPage' => $modelOverViewPage,'modelOverViewPageElement' => $modelOverViewPageElement,'data' => $sheetRanges['SNS']]) ?>
            <!-- form multi Overview  -->
            <?= $this->render('forms/_form-category',['form' => $form,'modelsSectionDinamic' => $modelsSectionDinamic,'items' => $sheetRanges['select'],'data' => $sheetRanges['Gráficos'] ]) ?>
            <!-- form multi Sac  -->
            <?= $this->render('forms/_form-sac',['form' => $form,'sacForm' => $sacForm,'items' => $sheetRanges['select'],'data' => $sheetRanges['SAC']]) ?>
            
            <div class="form-group">
                <?= Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-success']) ?>
            </div>
        </div>
    </div>
    <?php ActiveForm::end(); ?>
</div>
