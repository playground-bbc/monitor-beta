<?php 
use yii\helpers\Html;
?>

<!-- template que muestra cloud-view -->
<script type="text/x-template" id="cloud-view">
  <div v-if="loaded">
    <div class="row">
      <div class="col-md-12 well" v-for="item in resourcesObj">
  			<cloud-words :resourceId="item.id" :name="item.name"></cloud-words>
  		</div>
    </div>
    <cloud-dictionaries></cloud-dictionaries>
    <words-history></words-history>
  </div>
  <div v-else>
  	<div class="loader">
      <div class="spinner" style="height: 15vh;width:  15vh;"></div>
    </div>
  </div>
</script>

<!-- template que muestra las nubes de palabras -->
<script type="text/x-template" id="cloud-words">
    <div>
        <h2>Cloud words {{name}}</h2>
        <div :id="'jqcloud'+ resourceId"  class="jqcloud"></div>
    </div>
</script>

<!-- template que muestra las nubes de palabras del dictionario -->
<script type="text/x-template" id="cloud-dictionaries">
  <div class="row" v-if="loaded">
      <div  class="col-md-12 well">
        <h2>Cloud words dictionaries</h2>
        <div id="jqcloud-words-dictionaries"  class="jqcloud"></div>
      </div>
  </div>
</script>

<!-- template que muestra el grafico de las palabras con su cantidad -->
<script type="text/x-template" id="words-history">
  <div class="row">
      <div  class="col-md-12 well">
        <h2>Words historial</h2>
        <div id="history"></div>
      </div>
  </div>
</script>