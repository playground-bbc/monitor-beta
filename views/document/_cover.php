<?php 
use yii\helpers\Html;
// load dates to cover
$start_date = \Yii::$app->formatter->asDatetime($model->config->start_date,'dd/MM/yyyy');
$end_date   = \Yii::$app->formatter->asDatetime($model->config->end_date,'dd-MM/yyyy');
$new_time = date("d/m", $model->config->start_date);
$now = date("h:i d/m");
?>
<!-- images portada -->
<div class="row">
    <div class="col-md-12 text-center">
        <div class="">
                <?= Html::img($url_logo_small) ?>
                <br><br><br>
            <?= Html::img($url_logo,['height' => '500px','width' => '700px']) ?>
        </div>
    </div>
</div>
<!-- end images portada -->
<!-- leyend -->
<div class="row">
    <div class="col-md-12">
        <h3 style="font-family: 'Helvetica', sans-serif;">Reporte de Listening</h3>
        <h2 style="font-family: 'Helvetica', sans-serif;">Análisis</h2>
        <h4 style="font-family: 'Helvetica', sans-serif;"><?= $start_date ?> - <?= $end_date ?></h4>
        <p>Datos obtenidos de 12:00 <?= $new_time ?> al <?= $now ?></p>
    </div>
</div>
