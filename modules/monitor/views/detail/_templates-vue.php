<?php 
use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use kartik\select2\Select2;
use yii\widgets\ActiveForm;
?>


<!-- template que muestra los componentes-->
<script type="text/x-template" id="detail">
  <div>
  <pre>
    {{$data}}
  </pre>
  <hr> 
  <div v-if="!loading && count" class="col-md-12">
    <box-detail :isChange="isChange" :alertid="alertid" :resourceid="resourceid" :term="term"></box-detail>
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
            <small>{{box_property.total}}</small>
          </span>
        </div>
        <!-- /.info-box-content -->
      </div>
      <!-- /.info-box -->
    </div>
  </div> 
</script>