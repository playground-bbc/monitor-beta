<?php 
use wbraganca\dynamicform\DynamicFormWidget;
use yii\helpers\Html;
?>

<div class="panel panel-info">
    <div class="panel-heading">Slides Informativas</div>
    <div class="panel-body">
        <?php DynamicFormWidget::begin([
            'widgetContainer' => 'dynamicform_wrapper',
            'widgetBody' => '.container-items',
            'widgetItem' => '.section-item',
            'limit' => 20,
            'min' => 1,
            'insertButton' => '.add-section',
            'deleteButton' => '.remove-section',
            'model' => $modelsSection[0],
            'formId' => 'dynamic-form',
            'formFields' => [
                'head_title',
            ],
        ]); ?>
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Categoría</th>
                    <th style="width: 450px;">Sub Categoría</th>
                    <th class="text-center" style="width: 90px;">
                        <button type="button" class="add-section btn btn-success btn-xs"><span class="glyphicon glyphicon-plus"></span></button>
                    </th>
                </tr>
            </thead>
            <tbody class="container-items">
            <?php foreach ($modelsSection as $indexSection => $modelSection):  ?>
                <tr class="section-item">
                    <td class="vcenter">
                        <?php
                            // necessary for update action.
                            if (! $modelSection->isNewRecord) {
                                echo Html::activeHiddenInput($modelSection, "[{$indexSection}]id");
                            }
                        ?>
                        <?= $form->field($modelSection, "[{$indexSection}]head_title")->textInput(['maxlength' => true]) ?>
                    </td>
                    <td>
                        <?= $this->render('_form-page', [
                            'form' => $form,
                            'indexSection' => $indexSection,
                            'modelsPage' => $modelsPage[$indexSection],
                        ]) ?>
                    </td>
                    <td class="text-center vcenter" style="width: 90px; verti">
                        <button type="button" class="remove-section btn btn-danger btn-xs"><span class="glyphicon glyphicon-minus"></span></button>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php DynamicFormWidget::end(); ?>
    </div>
</div>

