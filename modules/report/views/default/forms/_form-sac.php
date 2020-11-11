<?php 
use kartik\typeahead\TypeaheadBasic;
?>
<div class="panel panel-warning">
    <div class="panel-heading">Slide SAC</div>
    <div class="panel-body">
        <div class="row">
            <div class="col-md-6">
                <?= $form->field($sacForm, 'head_title')->textInput(['placeholder' => 'SAC']) ?>
            </div>
            <div class="col-md-6">
                <?= $form->field($sacForm, 'titles[0]')->textInput(['placeholder' => 'SAC SNS Mes Año']) ?>
            </div>
        </div> 
        <ul class="list-group">
            <li class="list-group-item" style="padding: 0px 0px; border: 0px;">
                <div class="panel panel-default">
                    <div class="panel-heading">Slide Cuadros SAC SNS</div>
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-4">
                            <label for="tables_coordinates[0]">Rango Tabla Status de Mensajes</label>
                                <?= 
                                    // Usage with ActiveForm and model (with search term highlighting)
                                    $form->field($sacForm, 'tables_coordinates[0]')->widget(TypeaheadBasic::classname(), [
                                        'data' => $data,
                                        'options' => ['placeholder' => 'Filter as you type ...'],
                                        'pluginOptions' => ['highlight'=>true],
                                    ])->label(false);
                                
                                ?> 
                            </div>
                            <div class="col-md-4">
                            <label for="tables_coordinates[1]">Rango Tabla Resumen Checked</label>
                                <?= 
                                    // Usage with ActiveForm and model (with search term highlighting)
                                    $form->field($sacForm, 'tables_coordinates[1]')->widget(TypeaheadBasic::classname(), [
                                        'data' => $data,
                                        'options' => ['placeholder' => 'Filter as you type ...'],
                                        'pluginOptions' => ['highlight'=>true],
                                    ])->label(false);
                                
                                ?> 
                            </div>
                            <div class="col-md-4">
                            <label for="tables_coordinates[2]">Rango Tabla Respondido por</label>
                                <?= 
                                    // Usage with ActiveForm and model (with search term highlighting)
                                    $form->field($sacForm, 'tables_coordinates[2]')->widget(TypeaheadBasic::classname(), [
                                        'data' => $data,
                                        'options' => ['placeholder' => 'Filter as you type ...'],
                                        'pluginOptions' => ['highlight'=>true],
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
                            </div>
                            <div class="col-md-4">

                                <?php $checkbox = $form->field($sacForm, "mencion_by_categories_checked")->label(false)->checkbox([], false); ?>
                                <?= $form->field($sacForm, "mencion_by_categories",[
                                    'template' => "{label}<div class='input-group'><span class='input-group-addon' style='padding: 0px 12px;'>
                                    {$checkbox}
                                </span><div class='input-group'>{input}</div></div>{error}{hint}"
                                ]); ?>

                                
                            </div>
                            <div class="col-md-4">
                            </div>
                        </div>
                    </div>
                </div>    
            </li>
            <li class="list-group-item" style="padding: 0px 0px; border: 0px;">
                <div class="panel panel-default">
                    <div class="panel-heading">Slide Mensajes recibidos por red Social </div>
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-6">
                                <?= $form->field($sacForm, 'titles[1]')->textInput(['placeholder' => 'MENSAJES RECIBIDOS POR RED SOCIAL 2020']) ?>    
                            </div>
                            <div class="col-md-6">
                                <?= 
                                    // Usage with ActiveForm and model (with search term highlighting)
                                    $form->field($sacForm, 'tables_coordinates[3]')->widget(TypeaheadBasic::classname(), [
                                        'data' => $data,
                                        'options' => ['placeholder' => 'Filter as you type ...'],
                                        'pluginOptions' => ['highlight'=>true],
                                    ]);
                                
                                ?> 
                            </div>
                        </div>
                    </div>
                </div>    
            </li>
            <li class="list-group-item" style="padding: 0px 0px; border: 0px;">
                <div class="panel panel-default">
                    <div class="panel-heading">Slide Comentarios Positivos </div>
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-4">
                                <?= $form->field($sacForm, 'titles[2]')->textInput(['placeholder' => 'COMENTARIOS PÚBLICOS']) ?>               
                            </div>
                            <div class="col-md-4">
                                <?= 
                                    $form->field($sacForm, 'graph_coordinates[0]')
                                    ->dropDownList(
                                        $items,           // Flat array ('id'=>'label')
                                        ['prompt'=>'Selecione el Sheet donde se encuentre el grafico']   // options
                                    );
                                ?>    
                            </div>
                            <div class="col-md-4">
                                    <?= 
                                        $form->field($sacForm, 'graph_coordinates[1]')
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
                    <div class="panel-heading">Slide Mensajes Privados </div>
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-4">
                                <?= $form->field($sacForm, 'titles[3]')->textInput(['placeholder' => 'MENSAJES PRIVADOS']) ?>               
                            </div>
                            <div class="col-md-4">
                                    <?= 
                                        $form->field($sacForm, 'graph_coordinates[2]')
                                        ->dropDownList(
                                            $items,           // Flat array ('id'=>'label')
                                            ['prompt'=>'Selecione el Sheet donde se encuentre el grafico']   // options
                                        );
                                    ?> 
                            </div>
                            <div class="col-md-4">
                                    <?= 
                                        $form->field($sacForm, 'graph_coordinates[3]')
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
                    <div class="panel-heading">Slide Clasificacion tipos de Mensajes </div>
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-6">
                                <?= $form->field($sacForm, 'titles[4]')->textInput(['placeholder' => 'CLASIFICACIÓN DEL TIPO DE MENSAJE']) ?>
                            </div>
                            <div class="col-md-6">
                                <?= 
                                    // Usage with ActiveForm and model (with search term highlighting)
                                    $form->field($sacForm, 'tables_coordinates[4]')->widget(TypeaheadBasic::classname(), [
                                        'data' => $data,
                                        'options' => ['placeholder' => 'Filter as you type ...'],
                                        'pluginOptions' => ['highlight'=>true],
                                    ]);
                                    
                                ?> 
                                          
                                
                            </div>
                        </div>
                    </div>
                </div>    
            </li>
        </ul>
    </div>
</div>


