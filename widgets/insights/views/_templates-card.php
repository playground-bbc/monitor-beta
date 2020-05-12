<?php
use yii\helpers\Url;
use yii\helpers\Html;
?>
<!-- template que muestra el numero de card dependiendo de cuantos recursos -->
<script type="text/x-template" id="card-template">
	<div>
		<!-- /.col -->
		<div v-for="(item,index) in resources">
			<widget :length=resources.length  :resourceId= item.resource_id :index=index></widget>
			
		</div>
		<!-- /.col -->
	</div>
</script>

<!-- template que muestra el card vista -->
<script type="text/x-template" id="widget-template">
	<!-- Widget: user widget style 1 -->
	<div :class="'col-md-'+ (12/length)">
		
		<div class="card card-widget widget-user" style="width: 550px;">
			<!-- Add the bg color to the header using any of the bg-* classes -->
			<div class="widget-user-header bg-info":style="{backgroundImage: `url('${contentPage.image_url}')`, paddingTop: '10px'}">
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
						  <h5 class="description-header">{{insights.value | formatNumber }}</h5>
						  <span data-toggle="tooltip" data-placement="top" :title="insights.name  | setTitleTooltipsInsights" class="">{{insights.name  | setTitleInsights}}</span>
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
	            <tr>
	              <th scope="col">titulo post</th>
	              <th v-for="header in insightsHeader" data-placement="top" :title="header  | setHeaderToolTips" scope="col">{{header | setHeadersPost}}</th>
	            </tr>
	          </thead>
	          <tbody v-if="contentPosts">
	            <tr v-for="post in contentPosts">
	              <th scope="row"><a :href="post.permalink" data-placement="top" :title="post.message"  target="_blank">{{post.message  | stringSubstr}}</a></th>
	              <td align="center" v-for="insigth in post.wInsights">
	              		<div v-if="insigth.value">
	              			{{insigth.value  | formatNumber }}
	              		</div>
	              		<div v-else>
	              			{{insigth._like | isNullValue}} / {{parseInt(insigth._love) + parseInt(insigth._wow) + parseInt(insigth._haha) + parseInt(insigth._sorry) + parseInt(insigth._anger)}} 
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
					<th scope="row"><a :href="story.permalink" target="_blank">{{story.timespan | getDate }}</a></th>
		            <td align="center" v-for="insigth in story.wInsights">
	              		<div class="text-center" v-if="insigth.value">
	              			{{insigth.value  | formatNumber }}
	              		</div>
	              		<div v-else>
	              			{{insigth.value  | formatNumber }} 
	              		</div>
	          	  </td>
		        </tr>
		      </tbody>
		    </table>
		</div>
		<div v-else>
			<br>
			<p><strong>No se encontraron resultados para esta entidad!!</strong></p>
		</div>
	</div>	
</script>