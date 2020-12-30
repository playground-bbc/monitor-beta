<?php 
use yii\widgets\DetailView;
use yii\helpers\Html;
?>

<div class="row">
    <div class="col-md-12">
        <h4>descripción general</h4>
        <?= 
            DetailView::widget([
                'model' => $model,
                'attributes' => [
                    [
                        'label' => 'Status',
                        'value' => function($model){
                            return ($model->status) ? 'Activo' : 'Finalizada';
                        }
                    ],  
                    [
                        'label' => 'Nombre de la Alerta',
                        'value' => function($model){
                            return $model->name;
                        }
                    ],             
                    [   // the owner name of the model
                        'label' => 'Usuario',
                        'value' => $model->user->username,
                    ],
                    [
                        'label' => 'Creado en',
                        'value' => function($model){
                            return \Yii::$app->formatter->asDatetime($model->createdAt);
                        }
                    ],
                    [
                        'label' => 'Fecha Inicio',
                        'value' => function($model){
                            return \Yii::$app->formatter->asDatetime($model->config->start_date);
                        }
                    ],
                    [
                        'label' => 'Fecha Final',
                        'value' => function($model){
                            return \Yii::$app->formatter->asDatetime($model->config->end_date);
                        }
                    ],
                    [
                        'label' => Yii::t('app','Recursos Sociales'),
                        'format'    => 'raw',
                        'attribute' => 'alertResourceId',
                        'value' => function($model) {
                            $html = Html::ul($model->config->configSources, ['item' => function($resource, $index) {
                                return Html::tag(
                                    'li',
                                    $resource->alertResource->name,
                                // ['class' => 'post']
                                );
                            },'class' => 'list-inline']);
                            return $html;
                        },
        
                    ],
                    [
                        'label' => Yii::t('app','Scraping Paginas Web Urls'),
                        'format'    => 'raw',
                        'value' => function($model) {
                            $urls = explode(",",$model->config->urls);
                            $html = '';
                            foreach ($urls as $index => $url) {
                                $html .= " <span class='label label-default'><a style='color: white;' href='{$url}' target='_blank'>{$url}</a></span>";
                            }
                            return $html;
                        },
                        'visible' => ($model->config->urls != '')
                    ]
                    
                ],
            ]);
            
        ?>
    </div>
</div>

<?php if(!is_null($url_graph_count_sources)): ?>
<div class="row">
    <h4>Totales Globales</h4>
    <div class="col-md-12">
        <?= Html::img($url_graph_count_sources) ?>
    </div>
</div>
<?php endif; ?>

<?php if(!is_null($url_graph_date_sources)): ?>
<!-- break to another page -->
<div style='page-break-after:always'></div>
<!-- end break to another page -->  
<div class="row">
    <h4>Totales globales por fecha</h4>
    <div class="col-md-12">
        <?= Html::img($url_graph_date_sources) ?>
    </div>
</div>
<?php endif; ?>