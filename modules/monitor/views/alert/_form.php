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
                    <?= $form->field($alert, 'name')->textInput()->input('name', ['placeholder' => "Ingrese el nombre de la Alerta"]) ?>  
                </div>
            </div>
            <!-- dates -->
            <div class="row">
                <div class="col-md-6">
                    <?= $form->field($config, 'start_date')->widget(DatePicker::classname(), [
                            'type' => DatePicker::TYPE_INPUT,
                            'options' => ['id' => 'start_date','name' => 'start_date','placeholder' => 'Ingrese la fecha de inicio'],
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
                            'options' => ['id' => 'end_date','name' => 'end_date','placeholder' => 'Ingrese la fecha de finalización'],
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
            <div class="row overflow">
                <div class="col-md-4">
                    <?= $form->field($alert, 'alertResourceId')->widget(Select2::classname(), [
                            'data' => $alert->social,
                            'options' => [
                                'id' => 'social_resourcesId',
                                'placeholder' => 'Seleccione un recurso Social',
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
                            'placeholder' => 'Ingrese las Urls', 
                            'multiple' => true,
                        ],
                            'pluginOptions' => [
                                'tags' => true,
                                'tokenSeparators' => [',', ' '],
                                'minimumInputLength' => 2,
                                'maximumSelectionLength' => 8
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
                                'placeholder' => 'Seleccione los productos',
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
                <!-- <sync-product></sync-product> -->
            </div>
            <!-- config properties-->
            <div class="row">
                <div class="col-md-6">
                    <?= $form->field($alert, 'dictionaryIds')->widget(Select2::classname(), [
                            'data' => \yii\helpers\ArrayHelper::map(app\modules\wordlists\models\Dictionaries::find()
                            ->where(['<>','name', 'Free Words'])
                            ->andWhere(['<>','name', 'Product Competition'])
                            ->all(),'id','name'),
                            'options' => [
                                'id' => 'social_dictionaryId',
                                'resourceName' => 'dictionaries',
                                'placeholder' => 'Selecione Diccionarios de Palabras',
                                'multiple' => true,
                                'theme' => 'krajee',
                                'value' => (isset($alert->dictionariesIdsByAlert)) ? $alert->dictionariesIdsByAlert : [],
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
                <div class="col-md-6">
                    <?= $form->field($alert, 'free_words')->widget(Select2::classname(), [
                   // 'data' => $alert->freeKeywords,
                    'changeOnReset' => false,
                    'options' => [
                            'id' => 'free_words',
                            'resourceName' => 'Free Words',
                            'placeholder' => 'Indique palabras libres', 
                            'multiple' => true,
                          //  'value' => (isset($alert->freeKeywords)) ? $alert->freeKeywords : [],
                        ],
                        'pluginOptions' => [
                            'tags' => true,
                            'tokenSeparators' => [','],
                            'minimumInputLength' => 2
                        ],
                    ])->label('Palabras libres'); 
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