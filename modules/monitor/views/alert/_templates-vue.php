<?php 
use yii\helpers\Html;
?>
<!-- template que muestra el boton para solicitar el pdf -->
<script type="text/x-template" id="view-button-report">
  <button class="btn btn-info" v-on:click.prevent="send" v-bind:class="{ disabled: isdisabled}">Reporte</button>
</script>

<!-- template que muestra el total de todas las menciones -->
<script type="text/x-template" id="view-total-mentions">
     <div class="">
        <div class="col-md-2">
          <!-- small box -->
          <div class="small-box bg-info">
            <div class="inner">
              <h3>{{count | formatNumber }}</h3>

              <p>Total de Entradas</p>
            </div>
            <div class="icon">
              <i class="glyphicon glyphicon-hdd"></i>
            </div>
            <a href="#" class="small-box-footer">More info <i class="glyphicon glyphicon-chevron-right"></i></a>
          </div>
        </div>
        <!-- ./col -->
        <div class="col-md-2">
          <!-- small box -->
          <div class="small-box bg-info">
            <div class="inner">
              <h3>{{retweets | formatNumber }}</h3>

              <p>Retweets</p>
            </div>
            <div class="icon">
              <i class="socicon-twitter"></i>
            </div>
            <a href="#" class="small-box-footer">More info <i class="glyphicon glyphicon-chevron-right"></i></a>
          </div>
        </div>
        <!-- ./col -->
        <div class="col-md-2">
          <!-- small box -->
          <div class="small-box bg-success">
            <div class="inner">
              <h3>{{shares | formatNumber }}<sup style="font-size: 20px"></sup></h3>

              <p>Instagram Shares</p>
            </div>
            <div class="icon">
              <i class="glyphicon glyphicon-share"></i>
            </div>
            <a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
          </div>
        </div>
        <!-- ./col -->
        <div class="col-md-2">
          <!-- small box -->
          <div class="small-box bg-warning">
            <div class="inner">
              <h3>{{coments | formatNumber }}</h3>

              <p>Comentarios Totales</p>
            </div>
            <div class="icon">
              <i class="glyphicon glyphicon-comment"></i>
            </div>
            <a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
          </div>
        </div>
        <!-- ./col -->
        <div class="col-md-2">
          <!-- small box -->
          <div class="small-box bg-light">
            <div class="inner">
              <h3>{{likes | formatNumber }}</h3>

              <p>Instagram Likes</p>
            </div>
            <div class="icon">
              <i class="glyphicon glyphicon-heart"></i>
            </div>
            <a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
          </div>
        </div>
        <!-- ./col -->
        <!-- ./col -->
        <div class="col-md-2">
          <!-- small box -->
          <div class="small-box bg-danger">
            <div class="inner">
              <h3>{{likes_comments | formatNumber }}</h3>

              <p>likes comments</p>
            </div>
            <div class="icon">
              <i class="glyphicon glyphicon-thumbs-up"></i>
            </div>
            <a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
          </div>
        </div>
        <!-- ./col -->
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
  <div v-if="loaded">
    <div id="resources_chart_count"></div>
    <hr>
  </div>
  <div v-else>
        <div class="loader">
          <div class="spinner"></div>
        </div>
    </div>  
</script>

<script type="text/x-template" id="view-post-mentions-chart">
  <div v-if="render">
    <div v-if="loaded">
      <div id="post_mentions"></div>
      <hr>
    </div>
    <div v-else>
      <div class="loader">
        <div class="spinner"></div>
      </div>
    </div>
  </div>  
</script>

<!-- chart products interations -->
<script type="tex/x-template" id="view-products-interations-chart">
  <div v-if="loaded">
    <div id="products-interation-chart">
      
    </div>
    <hr>
  </div>
  <div v-else>
        <div class="loader">
          <div class="spinner"></div>
        </div>
    </div>  
</script>


<!-- template chart by date google chart -->
<script type="tex/x-template" id="view-date-resources-chart">
  <div v-if="loaded">
    <div id="date-resources-chart"></div>
    <hr>
  </div>
  <div v-else>
        <div class="loader">
          <div class="spinner"></div>
        </div>
    </div>  
</script>


<!-- template que muestra el total de todas las menciones por Red Social -->
<script type="text/x-template" id="view-total-mentions-resources">
    <div v-if="loaded">
        <div v-for="(value,resource) in response" class="col-md-2">
            <div class="well text-center">
              <h4><a href="#">{{resource}}:</a></h4>
                <p>{{value}}</p>
            </div>
        </div>
    </div>
    <div v-else>
        <div class="loader">
          <div class="spinner"></div>
        </div>
    </div>
</script>
<!-- template que muestra todas las menciones -->
<script type="text/x-template" id="mentions-list">
    <div>
        <h4>Menciones</h4>
        <div class="row">
            <div class="col-md-12">
                <table id="list-mentions" class="table table-striped table-bordered" cellspacing="0"  style="width:100%">
                    <thead>
                        <tr>
                            <th>Recurso</th>
                            <th>Producto</th>
                            <th>Fecha</th>
                            <th>Nombre</th>
                            <th>Usuario</th>
                            <th>Titulo</th>
                            <th>mensaje</th>
                            <th>Url</th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr>
                            <th>Recurso</th>
                            <th>Producto</th>
                            <th>Fecha</th>
                            <th>Nombre</th>
                            <th>Usuario</th>
                            <th>Titulo</th>
                            <th>mensaje</th>
                            <th>Url</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</script>
<!-- template que muestra las nubes de palabras -->
<script type="text/x-template" id="cloud-words">
    <div v-if="loaded" class="col-md-12 well">
        <h2>Nube de tags</h2>
        <i>Clickea la palabra para revisar los mensajes que contienen ese keyword</i>
        <hr>
        <button v-on:click.prevent="reload" class="btn btn-sm btn-primary" id="update-demo">Update</button>
        <div id="jqcloud" class="jqcloud"></div>
    </div>
</script>
<!-- template que muestra las tablas recurso: fecha - total -->
<script type="text/x-template" id="resource-date-mentions">
    <div v-if="loaded" class="panel-group" id="accordion">
      <div v-for="(values,resource,index) in response" class="panel panel-default">
        <div class="panel-heading">
          <h2 class="panel-title">
            <a data-toggle="collapse" data-parent="#accordion" :href="collapseValue('#',index)">
            <h2>{{resource}}</h2></a>
          </h2>
        </div>
        <div :id="collapseValue('',index)" class="panel-collapse collapse">
          <div class="panel-body">
            <table class="table table-striped table-bordered" cellspacing="0"  style="width:100%">
              <thead>
                  <tr>
                      <th>Producto</th>
                      <th>Fecha</th>
                      <th>Cant. Menciones</th>
                  </tr>
              </thead>
              <tfoot>
                  <tr v-for="value in values">
                      <th>{{value.product_searched}}</th>
                      <th>{{value.date}}</th>
                      <th>{{value.total}}</th>
                  </tr>
              </tfoot>
            </table>
          </div>
        </div>
      </div>
    </div>
    <div v-else>
        <div class="loader">
          <div class="spinner"></div>
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
<!-- template que muestra llos indicadores de cada red social -->
<script type="text/x-template" id="status-alert">
  <span class="status-indicator" v-bind:class= "colorClass"></span>
</script> 

<!-- template que muestra el modal -->
<script type="text/x-template" id="modal-alert">
</script> 