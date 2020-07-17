<?php 
use yii\helpers\Html;
use yii\helpers\Url;
?>
<!DOCTYPE html>
<html>
<head>
    <title></title>
    <meta charset="utf-8">
    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css?family=PT+Sans+Narrow:700&display=swap" rel="stylesheet">
    <script src="https://www.gstatic.com/charts/loader.js"></script>

</head>
<body>
    
    <div class="container">
        <!-- images portada -->
        <div class="row">
            <div class="col-md-12">
                <div class="">
                    <?= Html::img($url_logo_small) ?>
                    <br>
                    <?= Html::img($url_logo) ?>
                </div>
            </div>
        </div>
        <!-- end images portada -->
        <!-- leyend -->
        <div class="row">
            <div class="col-md-12">
                <h1 style="font-family: 'PT Sans Narrow', sans-serif;">Reporte Monitor</h1>
                <br>
            <p style="font-family: 'PT Sans Narrow', sans-serif;"><?=  Yii::$app->formatter->asDate('now', 'yyyy-MM-dd');  ?></p>
            <p>---</p>
            <p style="font-family: 'PT Sans Narrow', sans-serif; font-size: 16px; color: blue"><?= $model->user->username ?></p>

            <p style="font-family: 'PT Sans Narrow', sans-serif; font-size: 16px;">Social Media Trends</p>
            
            <p style="font-family: 'PT Sans Narrow', sans-serif; font-size: 16px;">Nombre de la Alerta: <?= $model->name ?></p>

            </div>
        </div>
        <!-- end  leyend -->
        <!-- break to another page -->
        <br><br><br><br><br><br><br><br>
        <!-- end break to another page -->
        <!-- top belt -->
        <div class="row">
            <div class="col-md-12">
                <div class="">
                    <?= Html::img($chart_bar_resources_count,['width'=>550,'height'=>180]) ?>
                </div>
            </div>
        </div>
        <!-- end top belt -->
        <div class="row">
            <div class="col-md-12">
                <?php if ($post_mentions): ?>
                    <?= Html::img($post_mentions,['width'=>550,'height'=>180]) ?>
                <?php endif ?>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <?= Html::img($products_interations,['width'=>550,'height'=>180]) ?>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                
            </div>
        </div>


        
    </div>


    <script type="text/php">
        if ( isset($pdf) ) {
            $x = 520;
            $y = 15;
            $text = "{PAGE_NUM} de {PAGE_COUNT}";
            $font = $fontMetrics->get_font("Arial, Helvetica, sans-serif", "normal");
            $size = 6;
            $color = array(255,0,0);
            $word_space = 0.0;  //  default
            $char_space = 0.0;  //  default
            $angle = 0.0;   //  default
            $pdf->page_text($x, $y, $text, $font, $size, $color, $word_space, $char_space, $angle);
        }
    </script>

    <!-- Latest compiled and minified JavaScript -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>

</body>
</html>