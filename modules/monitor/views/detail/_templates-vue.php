<?php 
use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use kartik\select2\Select2;
use yii\widgets\ActiveForm;
?>


<!-- template que muestra los componentes-->
<script type="text/x-template" id="detail">
  <div v-if="!loading && count" class="col-md-12">
    <box-detail>
  </div>
  <div v-else-if="loading">
      <div class="loader">
        <div class="spinner" style="height: 15vh;width:  15vh;"></div>
      </div>
  </div>
  <div v-else-if="!loading && !count">
    <div class="col-md-12">
      <div class="alert alert-info">
        <div v-html="msg"></div>
      </div>
    </div>
  </div>
</script>

<!-- box sources -->
<script type="text/x-template" id="box-info-detail">
  <div v-if="loaded" class="row">
    <div>
      <div class="">
        <div class="info-box">
          <span class="info-box-icon bg-info elevation-1"><i class="glyphicon glyphicon-hdd"></i></span>

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