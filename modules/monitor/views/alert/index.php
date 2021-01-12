<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;
use yii\web\JsExpression;
use yii\web\View;

use app\models\Resources;
use yii\grid\GridView;
//use macgyer\yii2materializecss\widgets\grid\GridView;
use kartik\select2\Select2;
use kartik\date\DatePicker;
/* @var $this yii\web\View */
/* @var $searchModel app\models\search\AlertSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Alerts';
$this->params['breadcrumbs'][] = $this->title;
$format = <<< JS
function format(data) {   
  var response="";

  if(data.id== 0 )
    response += '<i class="fa fa-clock-o mr5"></i>' + data.text;
  else if(data.id == 1)
    response += '<i class="fa fa-check mr5"></i>' + data.text;
  else
    response += '<i class="fa fa-times mr5"></i>' + data.text;
  return response;
}
JS;
$this->registerJs($format, View::POS_HEAD);
$this->registerJsFile('@web/js/app/index.js',[
  'depends' => [
        \app\assets\VueAsset::className(),
        \app\assets\SweetAlertAsset::className(),
        ],
  //'position' => [View::POS_END]      
]);

$escape = new JsExpression("function(m) { return m; }");

// if(isset(Yii::$app->user->identity->username)){
//     $usernames = ['admin'];
//     $template = (in_array(Yii::$app->user->identity->username,$usernames))? '{view}' : '{update}{delete}{view}';
// }

?>
<div class="alerts-index">
    <h1><?= Html::encode($this->title) ?></h1>
    <p>
        <?= Html::a('Crear Alerta', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>
    <?php Pjax::begin(['id' => 'alerts', 'timeout' => false, 'enablePushState' => false]) ?>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn',
             'contentOptions' => [
                  //  'style' => 'vertical-align: middle;'
                ]
            ],

            [
                'label' => Yii::t('app','Usuario'),
                'attribute' => 'userId',
                'format' => 'raw',
                'value' => function($model){
                    return Html::a($model->user->username,['view', 'id' => $model->id],['class' => ($model->userId != \Yii::$app->user->getId()) ? 'btn disabled' : '']);
                }
            ],
            [
                'label' => Yii::t('app','Nombre de la Alerta'),
                'attribute' => 'name',
                'format' => 'raw',
                'value' => function($model) {
                  return Html::a($model->name,['view', 'id' => $model->id],['class' => ($model->userId != \Yii::$app->user->getId()) ? 'btn disabled' : '']);
                }
            ],
            [
                'label' => Yii::t('app', 'Fecha de Inicio'),
                'attribute' => 'start_date',
                'format' => 'raw',
                'value' => function($model) { 
                    return Html::a(Yii::$app->formatter->asDatetime($model->config->start_date,'yyyy/MM/dd'), ['view', 'id' => $model->id],['class' => ($model->userId != \Yii::$app->user->getId()) ? 'btn disabled' : '']);
                },
                'filter' => DatePicker::widget([
                    'name' => 'AlertSearch[start_date]',
                    'type' => DatePicker::TYPE_COMPONENT_APPEND,
                    'value' => $searchModel['start_date'],
                   // 'layout' => $layout2,
                    'pluginOptions' => [
                        'autoclose' => true,
                        'format' => 'yyyy/mm/dd',
                    ]
                ]),
            ],
            [
                'label' => Yii::t('app', 'Fecha Final'),
                'attribute' => 'end_date',
                'format' => 'raw',
                'value' => function($model) { 
                    return Html::a(Yii::$app->formatter->asDatetime($model->config->end_date,'yyyy/MM/dd'), ['view', 'id' => $model->id],['class' => ($model->userId != \Yii::$app->user->getId()) ? 'btn disabled' : '']);
                },
                'filter' => DatePicker::widget([
                    'name' => 'AlertSearch[end_date]',
                    'type' => DatePicker::TYPE_COMPONENT_APPEND,
                    'value' => $searchModel['end_date'],
                    'pluginOptions' => [
                        'autoclose' => true,
                        'format' => 'yyyy/mm/dd',
                    ]
                ]),
            ],
            [
                'label' => Yii::t('app','Recurso Social'),
                'format'    => 'raw',
                'attribute' => 'alertResourceId',
                'filter' => Select2::widget([
                     'data' => \yii\helpers\ArrayHelper::map(Resources::find()->all(),'name','name'),
                     'name' => 'AlertSearch[alertResourceId]',
                     'value' => $searchModel['alertResourceId'],
                    // 'value' => isset($searchModel['alertResourceId']) ? $searchModel['alertResourceId'] : [],
                     'attribute' => 'alertResourceId',
                     'options' => ['placeholder' => 'Select resources...','multiple' => false],
                     'theme' => 'krajee',
                     'hideSearch' => true,
                     'pluginOptions' => [
                           'allowClear' => true,
                      ],
                ]),
                'value' => function($model) {
                    $html = '';
                    foreach ($model->config->configSources as $alert) {
                        $html .= " <span class='label label-info'>{$alert->alertResource->name}</span>";
                    }
                    return $html;
                }
            ],
            [
                'label' => Yii::t('app','Estado'),
                'format'    => 'raw',
                'attribute' => 'status',
                'filter' => Select2::widget([
                     'name' => 'AlertSearch[status]',
                     'value' => $searchModel['status'],
                     'attribute' => 'status',
                     'data' => [1 => 'Active', 0 => 'Inactive'],
                     'options' => ['placeholder' => 'Select status...'],
                     'theme' => 'krajee',
                     'hideSearch' => true,
                     'pluginOptions' => [
                           'allowClear' => true,
                      ],
                ]),
                'value' => function($model) use ($escape){
                   // return ($model->status) ? 'Active' : 'Inactive';
                   return Select2::widget([
                      'name' => 'AlertSearch[status]',
                      'value' => $model->status,
                      'data' => [1 => 'Active', 0 => 'Inactive'],
                      'hideSearch' => true,
                      'id' => $model->id,
                      'options' => [
                        'class' => 'changeStatus',
                        'propertyId' => $model->id,
                      ],
                      'pluginOptions' => [
                        'allowClear' => false,
                        'templateResult' => new JsExpression('format'),
                        'escapeMarkup' => $escape,
                      ],
                    ]);
                },
               'contentOptions' => ['style' => 'width: 10%;min-width: 20px'],     
            ],
            
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{update} {delete} {view} {report}',
                'contentOptions' => ['style' => 'width: 10%;min-width: 20px'], 
                'buttons' => [
                    'delete' => function($url, $model){
                        return Html::a('<span class="glyphicon glyphicon-trash"></span>', ['delete', 'id' => $model->id], [
                            'style' => ($model->userId != \Yii::$app->user->getId()) ? 'display: none;': '',
                            'title' => 'Eliminar la Alerta',
                            'data' => [
                                'confirm' => 'Are you absolutely sure ? You will lose all the information about this user with this action.',
                                'method' => 'post',
                            ],
                        ]);
                    },
                    'update' => function($url, $model){
                        return Html::a('<span class="glyphicon glyphicon-pencil"></span>', ['update', 'id' => $model->id], [
                            'style' => ($model->userId != \Yii::$app->user->getId()) ? 'display: none;': '',
                            'title' => 'Actualizar la Alerta'
                        ]);
                    },
                    'report' => function($url, $model){
                        $mentionCount = \app\helpers\MentionsHelper::getCountMentions($model);

                        return Html::a('<span class="glyphicon glyphicon-export"></span>', ['/monitor/pdf/document', 'alertId' => $model->id], [
                            'style' => (!$mentionCount['data']['count']) ? 'display: none;': '',
                            'title' => 'Descargar el Reporte'
                        ]);
                    }
                ]
            ],
        ],
    ]); ?>
    <?php Pjax::end() ?>

</div>
