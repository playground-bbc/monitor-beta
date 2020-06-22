<?php 
use yii\helpers\Html;
use kartik\grid\GridView;
use yii\widgets\Pjax;
use kartik\select2\Select2;
use yii\widgets\ActiveForm;

?>


<!-- template que muestra los componentes-->
<script type="text/x-template" id="detail">
  <div>
  <hr> 
  <div v-if="!loading && count" class="col-md-12">
    <box-detail :isChange="isChange" :alertid="alertid" :resourceid="resourceid" :term="term"></box-detail>
    <grid-detail :isChange="isChange" :alertid="alertid" :resourceid="resourceid" :term="term"></grid-detail>
  </div>
  <div v-else-if="loading">
      <div class="loader">
        <div class="spinner" style="height: 15vh;width:  15vh;"></div>
      </div>
  </div>
  <div v-else-if="!loading && count === 0">
    <div class="col-md-12">
      <div class="alert alert-info">
        <div v-html="msg"></div>
      </div>
    </div>
  </div>
  </div>
</script>

<!-- box sources -->
<script type="text/x-template" id="box-info-detail">
  <div  class="row">
    <div v-for="box_property in box_properties" :key="box_property.id" :class="calcColumns">
      <div  class="info-box">
        <span :class="box_property.background_color"><i :class="box_property.icon"></i></span>

        <div class="info-box-content">
          <span class="info-box-text"><small>{{box_property.title}}</small></span>
          <span class="info-box-number">
            <small>{{box_property.total | formatNumber }}</small>
          </span>
        </div>
        <!-- /.info-box-content -->
      </div>
      <!-- /.info-box -->
    </div>
  </div> 
</script>

<!-- grid mentions -->
<script type="text/x-template" id="grid-mention-detail">
  <div  class="row">
    <div class="col-md-12">
      <?php Pjax::begin(['id' => 'mentions-detail', 'timeout' => 10000, 'enablePushState' => false]) ?>
      <?=   $this->render('/alert/_search-word', ['model' => $searchModel,'view' => $view]); ?>
          <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'autoXlFormat'=>true,
            'krajeeDialogSettings' => ['overrideYiiConfirm' => false],
            'toggleDataContainer' => ['class' => 'btn-group mr-2'],
            'export'=>[
                'showConfirmAlert'=>false,
                'target'=> GridView::TARGET_BLANK
            ],
            'columns' => [
              [
                  'label' => Yii::t('app','Social id'),
                  'attribute' => 'social_id',
                  'format' => 'raw',
                  'value' => function($model){
                      return $model['social_id'];
                  }
              ],
              [
                  'label' => Yii::t('app','Recurso Social'),
                  'attribute' => 'resourceName',
                  'format' => 'raw',
                  'value' => function($model){
                      return $model['recurso'];
                  }
              ],
              [
                  'label' => Yii::t('app','Término buscado'),
                  'headerOptions' => ['style' => 'width:12%'],
                  'attribute' => 'termSearch',
                  'format' => 'raw',
                  'value' => function($model){
                      return $model['term_searched'];
                  }
              ],
                
                [
                    'label' => Yii::t('app','Fecha'),
                    'headerOptions' => ['style' => 'width:8%'],
                    //'attribute' => 'userId',
                    'format' => 'raw',
                    'value' => function($model){
                        return \Yii::$app->formatter->asDate($model['created_time'], 'yyyy-MM-dd');
                    }
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
</script>