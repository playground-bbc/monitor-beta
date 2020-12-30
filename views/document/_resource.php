<?php 
use yii\grid\GridView;
use yii\widgets\ListView;
?>
<!-- by resource -->
<div class="row">
    <div class="col-md-12">
        <?php  if(isset($values['terms']) && count($values['terms'])):?>
            <div class="page_break"></div>
            
            <h2><?= \Yii::$app->params['resourcesName'][$resourceName];?></h2>
            <?php foreach($values['terms'] as $term): ?>
                <p><?= $term ?></p>
            <?php endforeach; ?>

            <?php $url = $values['url_graph_data_terms'];?>
            <h4 style="font-family: 'Helvetica', sans-serif;">Totales por terminos</h4>
            <br><br>
            <div class="chart">
                <img src="<?= $url ?>" alt="Static Chart"/>
            </div>
            <?php if(isset($values['url_graph_common_words'])): ?> 
                <!-- break to another page -->
                <div style='page-break-after:always'></div>
                <!-- end break to another page -->   
                <?php $url = $values['url_graph_common_words'];?>
                <h4 style="font-family: 'Helvetica', sans-serif;">Palabras mas Comunes</h4>
                <br><br>
                <div class="chart">
                    <img src="<?= $url ?>" alt="Static Chart"/>
                </div>
            <?php endif; ?> 
            <?php if(isset($values['provider'])): ?> 
                <!-- break to another page -->
                <div style='page-break-after:always'></div>
                <!-- end break to another page -->  
                <h4 style="font-family: 'Helvetica', sans-serif;">Ultimas entradas</h4> 

                <table class="table">
                <thead>
                <tr>
                    <th><?= \Yii::t('app','Recurso') ?></th>
                    <th><?= \Yii::t('app','Termino') ?></th>
                    <th><?= \Yii::t('app','Nombre') ?></th>
                    <th><?= \Yii::t('app','Username') ?></th>
                    <th><?= \Yii::t('app','Mencion') ?></th>
                    <th><?= \Yii::t('app','url') ?></th>

                </tr>
                </thead>
                    <tbody>
                    <?= 
                            ListView::widget([
                                'dataProvider' => $values['provider'],
                                'itemView' => function ($model, $key, $index, $widget) {
                                    $itemContent = $this->render('_list',['model' => $model,'index' => $index]);
                        
                                    return $itemContent;
                        
                                    /* Or if you just want to display the list item only: */
                                    // return $this->render('_list_item',['model' => $model]);
                                },
                            ]);
                        
                        ?> 
                    </tbody>
                </table>



            <?php endif; ?> 
        <?php endif; ?>     
    </div>
</div>
<!-- end by resource-->