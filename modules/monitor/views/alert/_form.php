<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\bootstrap\Modal;
use yii\helpers\ArrayHelper;

use app\models\Products;
use app\models\Resources;

use kartik\select2\Select2;
use kartik\date\DatePicker;
use kartik\file\FileInput;
use mludvik\tagsinput\TagsInputWidget;



/* @var $this yii\web\View */
/* @var $model app\models\form\AlertForm */
/* @var $form ActiveForm */

$pluginOptions = [ 'allowClear' =>  true];
if (!$alert->isNewRecord) {
    $pluginOptions = [ 'allowClear' =>  false];
}

?>
<div id="views-alert" class="modules-monitor-views-alert">
    <?php $form = ActiveForm::begin(); ?>
        <div class="row">
            <div class="row">
                <div class="col-md-12">
                    <?= $form->field($alert, 'name') ?>  
                </div>
            </div>
            <!-- dates -->
            <div class="row">
                <div class="col-md-6">
                    <?= $form->field($config, 'start_date')->widget(DatePicker::classname(), [
                            'type' => DatePicker::TYPE_INPUT,
                            'options' => ['id' => 'start_date','name' => 'start_date','placeholder' => 'Enter start date ...'],
                            'pluginOptions' => [
                                'orientation' => 'down left',
                                'format' => 'dd/mm/yyyy',
                                'todayHighlight' => true,
                                'autoclose' => true,
                             //   'endDate' => '+28D',
                            ],
                            'pluginEvents' => [
                               "changeDate" => "function(e) {  validator_date(e); }",
                            ],
                        ]); 
                    ?>
                </div>
                <div class="col-md-6">
                    <?= $form->field($config, 'end_date')->widget(DatePicker::classname(), [
                        'type' => DatePicker::TYPE_INPUT,
                            'options' => ['id' => 'end_date','name' => 'end_date','placeholder' => 'Enter end date ...'],
                            'pluginOptions' => [
                                'orientation' => 'down left',
                                'format' => 'dd/mm/yyyy',
                                'todayHighlight' => true,
                                'autoclose' => true,
                            ],
                        ]); 
                    ?>
                </div>
            </div>
            <!-- dictionaries and social -->
            <div class="row">
                <div class="col-md-3">
                    <?= $form->field($alert, 'alertResourceId')->widget(Select2::classname(), [
                            'data' => $alert->social,
                            'options' => [
                                'id' => 'social_resourcesId',
                                'placeholder' => 'Select a resources...',
                                'multiple' => true,
                                'theme' => 'krajee',
                                'debug' => false,
                                'value' => (isset($alert->config->configSourcesByAlertResource)) 
                                            ? $alert->config->configSourcesByAlertResource : [],
                               
                            ],
                            'pluginOptions' => [
                                'allowClear' => true,
                            ],
                            'pluginEvents' => [
                               "select2:select" => "function(e) { 
                                    var resourceName = e.params.data.text; 
                                    return modalReosurces(resourceName);
                               }",
                            ],
                            'toggleAllSettings' => [
                               'selectLabel' => '',
                               'unselectLabel' => '',
                               'selectOptions' => ['class' => 'text-success'],
                               'unselectOptions' => ['class' => 'text-danger'],
                            ],
                        ]);
                    ?>
                </div>
                <div class="col-md-4">
                    <?= $form->field($config, 'urls')->widget(Select2::classname(), [
                        'options' => [
                            'id' => 'urls',
                            //'resourceName' => 'Product Competition',
                            'placeholder' => 'Ingrese url a Buscar', 
                            'multiple' => true
                        ],
                            'pluginOptions' => [
                                'tags' => true,
                                'tokenSeparators' => [',', ' '],
                                'minimumInputLength' => 2
                            ],
                        ]); 
                    ?>  
                </div>
                <div class="col-md-4">
                    <?= $form->field($alert, 'productsIds')->widget(Select2::classname(), [
                            'data' => Products::getProducts(),
                            'changeOnReset' => true,
                            'options' => [
                                'id' => 'productsIds',
                                'placeholder' => 'Select a products...',
                                'multiple' => true,
                                'theme' => 'krajee',
                               // 'debug' => true,
                                //'value' => [1 => 'LG G7 ThinQ (G710 / New Aurora Black'],
                            ],
                            'pluginOptions' => [
                                'allowClear' => false,
                                'tags' => false,
                            ],
                            /*'pluginEvents' => [
                               "select2:select" => "function(e) { 
                                    return null;
                               }",
                            ]*/
                        ]);
                    ?>
                </div>
                <sync-product></sync-product>
            </div>
            <!-- config properties-->
            <div class="row">
                <div class="col-md-4">
                    <?= $form->field($alert, 'dictionaryIds')->widget(Select2::classname(), [
                            'data' => $drive->dictionaries,
                            'options' => [
                                'id' => 'social_dictionaryId',
                                'resourceName' => 'dictionaries',
                                'placeholder' => 'Selecione Diccionarios de Palabras',
                                'multiple' => true,
                                'theme' => 'krajee',
                                'value' => (isset($alert->dictionariesName)) ? $alert->dictionariesName : [],
                            ],
                            'pluginOptions' => [
                                'allowClear' => false,
                            ],
                            'pluginEvents' => [
                               "select2:select" => "function(e) { 
                                    return null;
                               }",
                            ],
                            'toggleAllSettings' => [
                               'selectLabel' => '',
                               'unselectLabel' => '',
                               'selectOptions' => ['class' => 'text-success'],
                               'unselectOptions' => ['class' => 'text-danger'],
                            ],
                        ]);
                    ?>
                </div>
                <div class="col-md-4">
                    <?= $form->field($alert, 'free_words')->widget(Select2::classname(), [
                   // 'data' => $alert->freeKeywords,
                    'changeOnReset' => false,
                    'options' => [
                            'id' => 'free_words',
                            'resourceName' => 'Free Words',
                            'placeholder' => 'write a tags free words ...', 
                            'multiple' => true,
                          //  'value' => (isset($alert->freeKeywords)) ? $alert->freeKeywords : [],
                        ],
                        'pluginOptions' => [
                            'tags' => true,
                            'tokenSeparators' => [',', ' '],
                            'minimumInputLength' => 2
                        ],
                    ])->label('Tag free words'); 
                    ?>   
                </div>
                <div class="col-md-4">
                    <?= $form->field($config, 'competitors')->widget(Select2::classname(), [
                    //'data' => $data,
                    'options' => [
                        'id' => 'competitors',
                        'resourceName' => 'Product Competition',
                        'placeholder' => 'write a tags competitors ...', 
                        'multiple' => true,
                    ],
                        'pluginOptions' => [
                            'tags' => true,
                            'tokenSeparators' => [',', ' '],
                            'minimumInputLength' => 2
                        ],
                    ])->label('Tag competitors'); 
                    ?> 
                </div>
            </div>
            <!-- files -->
            <div class="row">
                <div class="col-md-12">
                    <?= $form->field($alert, 'files')->widget(FileInput::classname(), [
                        'name' => 'files',
                        'pluginOptions' => [
                            'showCaption' => false,
                            'showRemove' => true,
                            'showUpload' => false,
                            'browseClass' => 'btn btn-primary btn-block',
                            'browseIcon' => '<i class="glyphicon glyphicon-file"></i> ',
                            'browseLabel' =>  'Select File'
                        ],
                        'options' => ['accept' => 'text/xlsx'],
                        'pluginEvents' => [
                               "fileselect" => "function(e) { 
                                    var social = $('#social_resourcesId');
                                    var current_values = social.val();
                                    var data = {
                                        id: '8',
                                        text: 'Excel Document'
                                    };

                                    // Set the value, creating a new option if necessary
                                    if (social.find('option[value=' + data.id +']').length) {
                                        current_values.push(data.id);
                                        social.val(current_values).trigger('change');
                                    } else { 
                                        // Create a DOM Option and pre-select by default
                                        var newOption = new Option(data.text, data.id, true, true);
                                        // Append it to the select
                                        social.append(newOption).trigger('change');
                                    }
                               }",
                               "fileclear" => " function(e){ 
                                    var social = $('#social_resourcesId');
                                    var current_values = social.val();
                                    var index = current_values.indexOf(8);
                                    if(index === -1){
                                        current_values.splice(index, 1);
                                    }
                                    social.val(current_values).trigger('change');
                                }"
                        ]
                    ]); 
                    ?>
                </div>
            </div>
                     
            <div class="form-group">
                <?= Html::submitButton(Yii::t('app', 'Submit'), ['class' => 'btn btn-primary']) ?>
            </div>
        </div>
    <?php ActiveForm::end(); ?>
</div><!-- modules-monitor-views-alert -->

<!-- template que muestra el botton de reload -->
<script type="text/x-template" id="sync-product-id">
    <div class="col-md-1">
        <div class="form-group field-alerts-productsids">
            <button style="margin-top: 25px"  v-on:click.prevent="reload">{{msg}}</button>
        </div>
    </div>
</script>


<?php 
Yii::$app->view->registerJs('var appId = "'. Yii::$app->id.'"',  \yii\web\View::POS_HEAD);
$this->registerJsFile(
    '@web/js/app/form.js',
    ['depends' => [
        \app\assets\VueAsset::className(),
        \app\assets\SweetAlertAsset::className(),
        \app\assets\MomentAsset::className()
        ]
    ]
);

if (!$alert->isNewRecord) {
    Yii::$app->view->registerJs('var alertId = "'. $alert->id.'";var appId = "'. Yii::$app->id.'" ',  \yii\web\View::POS_HEAD);
    $this->registerJsFile(
    '@web/js/app/update.js',
    ['depends' => [
        \app\assets\SweetAlertAsset::className(),
        ]
    ]
);
}

?>