<?php
use yii\helpers\Url;
use yii\helpers\Html;
?>
<!-- template que muestra el boton para solicitar el pdf -->
<script type="text/x-template" id="card-template">
	<div>
		<!-- /.col -->
		<div v-for="(item,index) in resources">
			<widget :length=resources.length  :resourceId= item.resource_id :index=index></widget>
			
		</div>
		<!-- /.col -->
	</div>
</script>


<script type="text/x-template" id="widget-template">
<!-- Widget: user widget style 1 -->
<div :class="'col-md-'+ (12/length)">
	
	<div class="card card-widget widget-user" style="width: 550px;">
		<!-- Add the bg color to the header using any of the bg-* classes -->
		<div class="widget-user-header bg-info"style="background: url('https://scontent.fscl18-1.fna.fbcdn.net/v/t1.0-9/p720x720/92503651_10158133637707248_2934454774843572224_o.jpg?_nc_cat=108&_nc_sid=8024bb&_nc_ohc=_tPekMPQjMYAX-yUBha&_nc_ht=scontent.fscl18-1.fna&_nc_tp=6&oh=017dd4df74cef01f7eb6132b40ec8fe9&oe=5EB7F96D') center center; padding-top: 10px;">
		</div>
		<div v-if="contentPage.resource">
			<a :href="contentPage.permalink" class="widget-user-image" style="top: 55px;" target="_blank">
				<img class="img-circle elevation-2" :src="contentPage.resource.name | imagePath" >
			</a>
		</div>

		<div class="card-footer" style="padding-top: 10px;">
			<div class="row">
				<div v-for="(insights,index) in insightsPage" :class="getCol(insightsPage.length,index +1)">
					<div class="description-block">
					  <h5 class="description-header">{{insights.value}}</h5>
					  <span class="">{{insights.title}}</span>
					</div>
					<!-- /.description-block -->
				</div>
			</div>
			<hr>
			<div class="row">
			<div class="col-md-12">
					<ul class="nav nav-tabs">
						<li class="active">
							<a :href="'#' +idTab+'a'" data-toggle="tab">Posts Insights</a>
						</li>
						<li>
							<a :href="'#' +idTab+'b'" data-toggle="tab">Storys Insights</a>
						</li>
					</ul>
					<div class="tab-content clearfix">
						<posts :idTab=idTab :resourceId=resourceId></posts>
						<storys :idTab=idTab :resourceId=resourceId></storys>
						
					</div>		
				</div>	
			</div>
		<!-- /.row -->
		</div>
	</div>
</div>
<!-- /.widget-user -->
</script>


<script type="text/x-template" id="post-template">
	<div class="tab-pane active" :id="idTab+'a'">
		<div class="table-responsive">
			<table class="table">
	          <thead>
	            <tr v-if="insightsHeader">
	              <th scope="col">titulo post</th>
	              <th v-for="header in insightsHeader" scope="col">{{header}}</th>
	            </tr>
	          </thead>
	          <tbody v-if="contentPosts">
	            <tr v-for="post in contentPosts">
	              <th scope="row"><a :href="post.permalink" target="_blank">{{post.message  | stringSubstr}}</a></th>
	              <td align="center" v-for="insigth in post.wInsights">
	              		<div v-if="insigth.value">
	              			{{insigth.value}}
	              		</div>
	              		<div v-else>
	              			{{insigth._like | isNullValue}} / {{insigth._love | isNullValue}} 
	              		</div>
	          	  </td>
	            </tr>
	          </tbody>
	        </table>
		</div>
	</div>
</script>

<script type="text/x-template" id="insights-template">
<div class="tab-pane" :id="idTab+'b'">
	<div v-if="loaded" class="table-responsive">
		<table class="table">
	      <thead>
	        <tr v-if="storysHeader">
	          <th scope="col">Link</th>		
	          <th v-for="header in storysHeader" scope="col">{{header}}</th>
	          
	        </tr>
	      </thead>
	      <tbody v-if="contentStorys">
	        <tr v-for="story in contentStorys">
				<th scope="row"><a :href="story.permalink" target="_blank">Story</a></th>
	            <td align="center" v-for="insigth in story.wInsights">
              		<div class="text-center" v-if="insigth.value">
              			{{insigth.value}}
              		</div>
              		<div v-else>
              			{{insigth.value}} 
              		</div>
          	  </td>
	        </tr>
	      </tbody>
	    </table>
	</div>
	<div v-else>
		<br>
		<p><strong>No se encontraron resultados para esta entidad</strong></p>
	</div>
</div>	
</script>