<?php
use yii\helpers\Html;
use wbraganca\dynamicform\DynamicFormWidget;
?>


<?php DynamicFormWidget::begin([
    'widgetContainer' => 'dynamicform_inner',
    'widgetBody' => '.container-pages',
    'widgetItem' => '.room-pages',
    'limit' => 20,
    'min' => 1,
    'insertButton' => '.add-pages',
    'deleteButton' => '.remove-pages',
    'model' => $modelsPage[0],
    'formId' => 'dynamic-form',
    'formFields' => [
        'title'
    ],
]); ?>
<table class="table table-bordered">
    <thead>
        <tr>
            <th>Título de Cabecera</th>
            <th class="text-center">
                <button type="button" class="add-pages btn btn-success btn-xs"><span class="glyphicon glyphicon-plus"></span></button>
            </th>
        </tr>
    </thead>
    <tbody class="container-pages">
    <?php foreach ($modelsPage as $indexPage => $modelPage): ?>
        <tr class="room-pages">
            <td class="vcenter">
                <?php
                    // necessary for update action.
                    if (! $modelPage->isNewRecord) {
                        echo Html::activeHiddenInput($modelPage, "[{$indexSection}][{$indexPage}]id");
                    }
                ?>
                <?= $form->field($modelPage, "[{$indexSection}][{$indexPage}]title")->label(false)->textInput(['maxlength' => true]) ?>
            </td>
            <td class="text-center vcenter" style="width: 90px;">
                <button type="button" class="remove-pages btn btn-danger btn-xs"><span class="glyphicon glyphicon-minus"></span></button>
            </td>
        </tr>
     <?php endforeach; ?>
    </tbody>
</table>
<?php DynamicFormWidget::end(); ?>