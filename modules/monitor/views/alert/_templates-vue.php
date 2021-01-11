<?php 
use yii\helpers\Html;
use kartik\grid\GridView;
use yii\widgets\Pjax;
use kartik\select2\Select2;
use kartik\export\ExportMenu;
use yii\widgets\ActiveForm;


?>
<!-- template que muestra el boton para solicitar el pdf -->
<script type="text/x-template" id="view-button-report">
  <button class="btn btn-info" v-on:click.prevent="send" v-bind:class="{ disabled: isdisabled}">Reporte</button>
</script>

<!-- template que muestra los indicadores de cada red social -->
<script type="text/x-template" id="status-alert">
  <span class="status-indicator" v-bind:class= "colorClass"></span>
</script> 

<!-- template que muestra el total de todas las menciones -->
<script type="text/x-template" id="view-total-mentions">
     <div class="row seven-cols">
        <div v-for="(value,resource) in resourcescount" :class="calcColumns()">
          <!-- small box -->
          <div v-if="getClass(resource)" :class="getClass(resource)">
            <div class="inner">
              <h3>{{value | formatNumber }}</h3>

              <p>{{getTitle(resource)}}</p>
            </div>
            <div class="icon">
              <i :class="getIcon(resource)"></i>
            </div>
            <a  :href="getLink(resource)" target="_blank" class="small-box-footer">More info <i class="glyphicon glyphicon-chevron-right"></i></a>
          </div>
        </div>
        
      </div>
</script>

<!-- box sources -->
<script type="text/x-template" id="view-box-sources">
  <div v-if="loaded" class="row" v-bind:class="{seven: isseven}">
    <div v-for="index in counts">
      <div :class="calcColumns()">
        <div class="info-box">
          <span class="info-box-icon bg-info elevation-1"><i :class="getIcon(response[index -1][0])"></i></span>

          <div class="info-box-content">
            <span class="info-box-text"><small>{{response[index -1][0]  | ensureRightPoints }}</small></span>
            <span class="info-box-number">
              {{response[index -1][1]}}
              <small></small>
            </span>
          </div>
          <!-- /.info-box-content -->
        </div>
        <!-- /.info-box -->
      </div>
    </div> 
  </div> 
</script>

<!-- template chart google -->
<script type="tex/x-template" id="view-total-resources-chart">
  <div>
    <div v-show="loaded">
      <div id="resources_chart_count"></div>
    </div>
    <div v-show="!loaded">
      <div class="loader">
        <div class="spinner"></div>
      </div>
    </div> 
  </div> 
</script>

<script type="text/x-template" id="view-post-mentions-chart">
  <div>
    <div v-show="loaded">
      <div id="post_mentions"></div>
    </div>
    <div v-show="!loaded">
      <div class="loader">
        <div class="spinner"></div>
      </div>
    </div>
  </div>  
</script>

<!-- chart products interations -->
<script type="tex/x-template" id="view-products-interations-chart">
  <div>
    <div v-show="loaded">
      <div id="products-interation-chart">
        
      </div>
    </div>
    <div v-show="!loaded">
          <div class="loader">
            <div class="spinner"></div>
          </div>
      </div> 
  </div> 
</script>

<!-- chart common words -->
<script type="tex/x-template" id="view-count-common-words-chart">
  <div v-show="loaded">
    <div id="container-common-words"></div>
    
  </div>
</script>

<!-- chart retails -->
<script type="tex/x-template" id="view-count-domains-chart">
  <div v-show="loaded">
    <div id="view-count-domains-chart"></div>
    
  </div>
</script>

<!-- template que muestra todas las menciones -->
<script type="text/x-template" id="mentions-list">
  <div>
    <div class="row">
     
      <div class="col-md-12">
        <!-- <button v-on:click="reload">Reload</button> -->
        <?php Pjax::begin(['id' => 'mentions', 'timeout' => 10000, 'enablePushState' => false]) ?>
        
            <?=  $this->render('_search-word', ['model' => $searchModel]); ?>
            <?= GridView::widget([
              'dataProvider' => $dataProvider,
              'filterModel' => $searchModel,
              'export' => true,
              'autoXlFormat'=>true,
              'krajeeDialogSettings' => ['overrideYiiConfirm' => false],
              'toggleDataContainer' => ['class' => 'btn-group mr-2'],
              'export'=>[
                'showConfirmAlert'=>false,
                'target'=>GridView::TARGET_BLANK
              ],
              'exportConfig' => [
                GridView::TEXT => ['label' => 'Guardar como Texto'],
                GridView::EXCEL => ['label' => 'Guardar como Excel'],
                GridView::PDF => ['label' => 'Guardar como Pdf'],
                GridView::JSON => ['label' => 'Guardar como JSON'],
              ],
              'toggleDataOptions' =>[
                'all' => [
                    'icon' => '',
                    'label' => '',
                    'class' => '',
                    'title' => ''
                ],
              ],
              'toolbar' => [
                '{export}',
                [
                  'content'=>
                    Html::a('<i class="glyphicon glyphicon-export"></i> Exportar todas las Menciones',
                      ['//monitor/pdf/export-mentions-excel','alertId' => $model->id],
                      [
                        'title'=>'Exportar todas las Menciones', 
                        'target'=>'_blank',
                        'data-pjax' => 0,
                        'class'=>'btn btn-outline-secondary btn-default',
                      ]
                    ),
                ]
              ],
              'columns' => [
                [
                      'label' => Yii::t('app','Recurso Social'),
                      'attribute' => 'resourceName',
                      'format' => 'raw',
                      'value' => function($model){
                          return $model['recurso'];
                      },
                      'filter' => Select2::widget([
                          'data' => \yii\helpers\ArrayHelper::map($model->config->sources,'name','name'),
                          'name' => 'MentionSearch[resourceName]',
                          'value' => $searchModel['resourceName'],
                          'attribute' => 'resourceName',
                          'options' => ['placeholder' => 'Select resources...','multiple' => false],
                          'theme' => 'krajee',
                          'hideSearch' => true,
                          'pluginOptions' => [
                                'allowClear' => true,
                            ],
                      ]),
                  ],
                  [
                      'label' => Yii::t('app','Término buscado'),
                      'headerOptions' => ['style' => 'width:12%'],
                      'attribute' => 'termSearch',
                      'format' => 'raw',
                      'value' => function($model){
                          return $model['term_searched'];
                      },
                      'filter' => Select2::widget([
                          'data' => $model->termsFind,
                          'name' => 'MentionSearch[termSearch]',
                          'value' => $searchModel['termSearch'],
                          'attribute' => 'termSearch',
                          'options' => ['placeholder' => 'Select term...','multiple' => false],
                          'theme' => 'krajee',
                          'hideSearch' => true,
                          'pluginOptions' => [
                                'allowClear' => true,
                            ],
                      ]),
                  ],
                  [
                      'label' => Yii::t('app','Fecha'),
                      'headerOptions' => ['style' => 'width:25%'],
                      'attribute' => 'created_time',
                      'format' => 'raw',
                      'value' => function($model){
                          return \Yii::$app->formatter->asDate($model['created_time'], 'yyyy-MM-dd');
                      },
                      'filter' => \kartik\date\DatePicker::widget([
                          'name' => 'MentionSearch[created_time]',
                          'type' => \kartik\date\DatePicker::TYPE_COMPONENT_APPEND,
                          'value' => $searchModel['created_time'],
                        // 'layout' => $layout2,
                          'pluginOptions' => [
                              'autoclose' => true,
                              'format' => 'yyyy/mm/dd',
                          ]
                      ]),
                  ],
                  [
                      'label' => Yii::t('app','Nombre'),
                      'attribute' => 'name',
                      'format' => 'raw',
                      'value' => function($model){
                          return $model['name'];
                      }
                  ],
                  [
                      'label' => Yii::t('app','Username'),
                      'attribute' => 'screen_name',
                      'format' => 'raw',
                      'value' => function($model){
                          return $model['screen_name'];
                      }
                  ],
                  [
                      'label' => Yii::t('app','Titulo'),
                      'attribute' => 'subject',
                      'format' => 'raw',
                      'value' => function($model){
                          return $model['subject'];
                      }
                  ],
                  [
                      'label' => Yii::t('app','Mencion'),
                      'attribute' => 'message_markup',
                      'format' => 'raw',
                      'value' => function($model){
                          return $model['message_markup'];
                      }
                  ],
                  [
                      'label' => Yii::t('app','Url'),
                      //'attribute' => 'userId',
                      'format' => 'raw',
                      'value' => function($model){
                          return \yii\helpers\Html::a('link',$model['url'],['target'=>'_blank', 'data-pjax'=>"0"]);
                      }
                  ],
              ],
              'class' => 'yii\grid\Column',
              'pjax'=>false,
              'pjaxSettings'=>[
                'options'=>[
                  'id'=> 'mentions'
                ]
              ],
              'showPageSummary'=>true,
              'panel'=>[
                  'type'=>'primary',
                  'heading'=>'Menciones'
              ],
            ]); ?>
        <?php Pjax::end() ?>
      </div>
    </div>
  </div>
</script>

<!-- template que muestra las nubes de palabras -->
<script type="text/x-template" id="cloud-words">
    <div v-if="loaded" class="col-md-12 well">
        <h2>Nube de tags</h2>
        <i>Clickea la palabra para revisar los mensajes que contienen ese keyword</i>
        <button v-on:click.prevent="reload" class="btn btn-sm btn-primary" id="update-demo">Update</button>
        <div id="jqcloud" class="jqcloud"></div>
    </div>
</script>


<!-- template que muestra las tablas recurso: higchart fecha - total -->
<script type="text/x-template" id="view-date-chart">
  <div>
    <!-- <div id="date"></div>        -->
    <div v-show="loaded">
      <div id="date"></div>  
    </div>
    <div v-show="!loaded">
        <div class="loader">
          <div class="spinner"></div>
        </div>
    </div>
  </div>
</script>

<!-- template que muestra la tabla de lista de emojis -->
<script type="text/x-template" id="emojis-list">
    <div v-if="loaded" class="panel-group" id="accordion">
      <div class="panel panel-default">
        <div class="panel-heading">
          <h2 class="panel-title">
            <a data-toggle="collapse" data-parent="#accordion" href="#emoji1">
            <h2>Listas de Emojis</h2></a>
          </h2>
        </div>
        <div id="emoji1" class="panel-collapse collapse">
          <div class="panel-body">
            <table class="table table-striped table-bordered" cellspacing="0"  style="width:100%">
              <thead>
                  <tr>
                      <th>Nombre</th>
                      <th>Emojis</th>
                      <th>Count</th>
                  </tr>
              </thead>
              <tfoot>
                  <tr v-for="(emojis,name,index) in response">
                      <th>{{name}}</th>
                      <th>{{emojis.emoji}}</th>
                      <th>{{emojis.count}}</th>
                  </tr>
              </tfoot>
            </table>
          </div>
        </div>
      </div>
    </div>
</script>

<!-- template que muestra el modal -->
<script type="text/x-template" id="modal-alert">
</script> 