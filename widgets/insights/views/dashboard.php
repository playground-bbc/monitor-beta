<?php 
\app\assets\AxiosAsset::register($this);
app\widgets\insights\assets\InsightsAsset::register($this);
?>
<div id="insights" class="container">
	<!-- <h3 class="mt-4 mb-4">Social Widgets</h3> -->
	<div class="row">
		<div v-if="loaded">
			<card-widget :resources = "resources">
		</div>
		<div v-else>
			<?php 
				echo \yii\bootstrap\Alert::widget([
				    'options' => [
				        'class' => 'alert-info',
				    ],
				    'body' => 'Opps no tenemos Insights disponibles'
				]);

			?>
		</div>	
	</div>
<!-- /.row -->
</div>

<?= $this->render('_templates-card');  ?>
