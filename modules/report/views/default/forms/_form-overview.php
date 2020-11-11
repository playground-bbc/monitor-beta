<?php 
use kartik\typeahead\TypeaheadBasic;
$overviewValues = ['alt_table_fans','alt_table_engament','alt_table_sac'];
$overviewLabels = ['Tabla Fans/Seguidores rango','Tabla Engagement rango','Tabla SAC rango'];
?>

<div class="panel panel-success">
    <div class="panel-heading">Slide Overview </div>
    <div class="panel-body">
        <div class="row">
            <div class="col-md-6">
                <?= $form->field($modelOverViewSection, '[fixed]head_title')->textInput() ?>
            </div>
            <div class="col-md-6">
                <?= $form->field($modelOverViewPage, '[fixed]title')->textInput() ?>
            </div>
        </div>

        <div class="row">
            <?php foreach($modelOverViewPageElement as $indexPageElement => $PageElement): ?>
                <div class="col-md-4">
                    <?= $form->field($PageElement, "[{$indexPageElement}]name[]")->hiddenInput(['value'=> $overviewValues[$indexPageElement]])->label(false); ?>
                    <label for="[{$indexPageElement}]value"><?= $overviewLabels[$indexPageElement] ?></label>
                    <?= 
                        // Usage with ActiveForm and model (with search term highlighting)
                        $form->field($PageElement, "[{$indexPageElement}]value")->widget(TypeaheadBasic::classname(), [
                            'data' => $data,
                            'options' => ['placeholder' => 'Filter as you type ...'],
                            'pluginOptions' => ['highlight'=>true],
                        ])->label(false);
                    
                    ?>
                </div>
                        
            <?php endforeach; ?> 
        </div>

        
    </div>
</div>    

