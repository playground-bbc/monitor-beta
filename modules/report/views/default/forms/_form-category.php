<?php 
use wbraganca\dynamicform\DynamicFormWidget;
use kartik\typeahead\TypeaheadBasic;
?>

<?php DynamicFormWidget::begin([
    'widgetContainer' => 'dynamicform_wrapper_dinamic',
    'widgetBody' => '.container-items-dinamic',
    'widgetItem' => '.section-item-dinamic',
    'limit' => 10,
    'min' => 1,
    'insertButton' => '.add-section-dinamic',
    'deleteButton' => '.remove-section-dinamic',
    'model' => $modelsSectionDinamic[0],
    'formId' => 'dynamic-form',
    'formFields' => [
        'head_title',
    ],
]); ?>

<div class="panel panel-default">
    <div class="panel-heading">
        <button type="button" class="pull-right add-section-dinamic btn btn-success btn-xs"><i class="glyphicon glyphicon-plus"></i> Añadir Categoria</button>
        <div class="clearfix"></div>
    </div>
    <div class="panel-body container-items-dinamic"><!-- widgetContainer -->
        <?php foreach ($modelsSectionDinamic as $indexindexSection => $modelSectionDinamic): ?>
            <div class="section-item-dinamic panel panel-info"><!-- widgetBody -->
                <div class="panel-heading">
                    <span class="panel-title-address">Slide Categorias: <?= ($indexindexSection + 1) ?></span>
                    <button type="button" class="pull-right remove-section-dinamic btn btn-danger btn-xs"><i class="fglyphicon glyphicon-minus"></i></button>
                    <div class="clearfix"></div>
                </div>
                <div class="panel-body">
                    <?php
                        // necessary for update action.
                        if (!$modelSectionDinamic->isNewRecord) {
                            echo Html::activeHiddenInput($modelSectionDinamic, "[{$indexindexSection}]id");
                        }
                    ?>
                    <div class="row">
                        <div class="col-md-6">
                            <?= $form->field($modelSectionDinamic, "[{$indexindexSection}]head_title")->textInput(['maxlength' => true]) ?>
                        </div>
                        <div class="col-md-6">
                            <?= $form->field($modelSectionDinamic, "[{$indexindexSection}]title")->textInput(['maxlength' => true]) ?>
                        </div>
                    </div>

                    <ul class="list-group">
                        <li class="list-group-item" style="padding: 0px 0px; border: 0px;">
                            <div class="panel panel-default">
                                <div class="panel-heading">Slide Distribución de contenidos</div>
                                    <div class="panel-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <?= 
                                                    $form->field($modelSectionDinamic, "[{$indexindexSection}]graphic_facebook")
                                                    ->dropDownList(
                                                        $items,           // Flat array ('id'=>'label')
                                                        ['prompt'=>'Selecione el Sheet donde se encuentre el grafico']    // options
                                                    );
                                                ?>
                                            </div>
                                            <div class="col-md-6">
                                                <?= 
                                                    $form->field($modelSectionDinamic, "[{$indexindexSection}]graphic_instagram")
                                                    ->dropDownList(
                                                        $items,           // Flat array ('id'=>'label')
                                                        ['prompt'=>'Selecione el Sheet donde se encuentre el grafico']   // options
                                                    );
                                                
                                                ?>
                                            </div>
                                        </div>
                                    </div>
                            </div>    
                        </li> 
                        <li class="list-group-item" style="padding: 0px 0px; border: 0px;">
                            <div class="panel panel-default">
                                <div class="panel-heading">Resultados comparativos de la categoría</div>
                                <div class="panel-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                        <label for="[{$indexindexSection}]table_facebook">Rango Tabla Facebook </label>
                                            <?= 
                                                // Usage with ActiveForm and model (with search term highlighting)
                                                $form->field($modelSectionDinamic, "[{$indexindexSection}]table_facebook")->widget(TypeaheadBasic::classname(), [
                                                    'data' => $data,
                                                    'options' => ['placeholder' => 'Filter as you type ...'],
                                                    'pluginOptions' => ['highlight'=>true,'id' => "[{$indexindexSection}]table_facebook"],
                                                ])->label(false);
                                            
                                            ?>
                                        </div>
                                        <div class="col-md-6">
                                        <label for="[{$indexindexSection}]table_instagram">Rango Tabla Instagram </label>
                                            <?= 
                                                // Usage with ActiveForm and model (with search term highlighting)
                                                $form->field($modelSectionDinamic, "[{$indexindexSection}]table_instagram")->widget(TypeaheadBasic::classname(), [
                                                    'data' => $data,
                                                    'options' => ['placeholder' => 'Filter as you type ...'],
                                                    'pluginOptions' => ['highlight'=>true,'id' => "[{$indexindexSection}]table_instagram"],
                                                ])->label(false);
                                            
                                            ?>        
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li> 
                        <li class="list-group-item" style="padding: 0px 0px; border: 0px;">
                            <div class="panel panel-default">
                                <div class="panel-heading">Slide Opcionales </div>
                                <div class="panel-body">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="input-group">
                                                <?php $checkbox = $form->field($modelSectionDinamic, "[{$indexindexSection}]sentiment_by_category_checked")->label(false)->checkbox([], false); ?>
                                                <?= $form->field($modelSectionDinamic, "[{$indexindexSection}]sentiment_by_category", [
                                                    'template' => "{label}<div class='input-group'><span class='input-group-addon' style='padding: 0px 12px;'>
                                                    {$checkbox}
                                                </span><div class='input-group'>{input}</div></div>{error}{hint}"
                                                ]); ?>
                                            </div>
                                        </div>        
                                        <div class="col-md-4">

                                        <?php $checkbox = $form->field($modelSectionDinamic, "[{$indexindexSection}]message_by_product_category_checked")->label(false)->checkbox([], false); ?>
                                                <?= $form->field($modelSectionDinamic, "[{$indexindexSection}]message_by_product_category", [
                                                    'template' => "{label}<div class='input-group'><span class='input-group-addon' style='padding: 0px 12px;'>
                                                    {$checkbox}
                                                </span><div class='input-group'>{input}</div></div>{error}{hint}"
                                                ]); ?>

                                        </div>
                                        <div class="col-md-4">
                                        <?php $checkbox = $form->field($modelSectionDinamic, "[{$indexindexSection}]case_succes_sac_checked")->label(false)->checkbox([], false); ?>
                                                <?= $form->field($modelSectionDinamic, "[{$indexindexSection}]case_succes_sac", [
                                                    'template' => "{label}<div class='input-group'><span class='input-group-addon' style='padding: 0px 12px;'>
                                                    {$checkbox}
                                                </span><div class='input-group'>{input}</div></div>{error}{hint}"
                                                ]); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>    
                        </li>  
                    <ul>                                    
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?php DynamicFormWidget::end(); ?>     