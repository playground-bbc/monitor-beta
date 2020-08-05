<?php 
use yii\web\UrlManager;
$um = new UrlManager;

?>
<div class="monitor-default-index">
    <h3>Reporte de la Alerta</h3>
    <ul>
        <li>Nombre de Alerta: <?= $data['alert']->name ?></li>
        
        <li>Fecha de Inicio: <?= \Yii::$app->formatter->asDatetime($data['alert']->config->start_date,'yyyy/MM/dd')?></li>
        <li>Fecha de Final: <?= \Yii::$app->formatter->asDatetime($data['alert']->config->end_date,'yyyy/MM/dd')?></li>
    </ul>
    <p>
    <table class="table display product-overview mb-30">
        
            <tr>
            <?php foreach($data['sources'] as $sourceName  => $properties): ?>
            <!-- http://domain/monitor-beta/web/monitor/detail?id=4&resourceId=1 -->
            <th colspan="2"><?= $sourceName ?></th>
            <?php endforeach; ?>
            </tr>
            
            <tr>
            <?php foreach($data['sources'] as $sourceName  => $properties): ?>
            <th>Palabra/Oracion</th>
            <th>Total</th>
            <?php endforeach; ?>   
            </tr>
        
        <?php 
            // Using array_keys() function 
            $key = array_keys($data['sources']); 
            // Calculate the size of array 
            $size = sizeof($key); 
        
        ?> 
        <?php for ($i=0; $i < 5; $i++) :?>
        <tr>
            <?php for($j =0 ; $j < $size; $j++): ?>
                <td style="text-align:center"><?= $data['sources'][$key[$j]]['words'][$i]['name'] ?></td>
                <td style="text-align:center"><?= $data['sources'][$key[$j]]['words'][$i]['total'] ?></td>
            <?php endfor; ?>    

        </tr>
        <?php endfor; ?>     
    </table>
    </p>
    
    <p>
        Este email es de Prueba, por favor no responda este email.<br>
        
    </p>
</div>