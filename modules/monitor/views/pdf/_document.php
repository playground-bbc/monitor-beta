<?php 
use yii\helpers\Html;
use yii\helpers\Url;

$start_date = \Yii::$app->formatter->asDatetime($model->config->start_date,'dd/MM/yyyy');
$end_date   = \Yii::$app->formatter->asDatetime($model->config->end_date,'dd-MM/yyyy');
$new_time = date("d/m", $model->config->start_date);
$now = date("H:i d/m");

$resourcesName = [
    "Twitter" => "Twitter",
    "Live Chat" => "Live Chat (Tickets)",
    "Live Chat Conversations" => "Live Chat (Chats)",
    "Facebook Comments" => "Facebook Commentarios",
    "Instagram Comments" => "Instagram Commentarios",
    "Facebook Messages" => "Facebook Inbox",
    "Excel Document" => "Excel Documento",
    "Paginas Webs" => "Paginas Webs",
];

?>
<!DOCTYPE html>
<html>
<head>
    <title></title>
    <meta charset="utf-8">
</head>
<body>
    <!-- <link rel="stylesheet" href="../css/Socicon/style.css"> -->
    <style>
            
            * {
            box-sizing: border-box;
            }
            
            .page_break { page-break-before: always; }
            .chart{
                width: 300px;
                height: 300px;
            }
    </style>
    <div class="container">
        <!-- images portada -->
        <div class="row">
            <div class="col-md-12">
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
        <!-- end  leyend -->
       
        <br><br><br><br><br><br>
        
       <!-- break to another page -->
       <div class="page-break"></div>
       <!-- end break to another page -->
        
        <div class="row">
            <div class="col-md-12">
                <!-- show  terms searched -->
                <h2 style="font-family: 'Helvetica', sans-serif;"><?= $model->name ?></h2>
                <h1 style="font-family: 'Helvetica', sans-serif;">Escucha</h1>

                <?php foreach($model->products as $term): ?>
                    <p><?= $term ?></p>
                <?php endforeach; ?>  
                <!-- end show  terms searched -->
            </div>
        </div>
        
        <!-- break to another page -->
       <div class="page-break"></div>
       <!-- end break to another page -->
       
       <!-- by resource -->
       <div class="row">
           <div class="col-md-12">
            <?php foreach($resourcesSocialData as $resourceName  => $values) :?> 
                    <div class="page_break"></div>
                    
                    <h2><?= $resourcesName[$resourceName] ?></h2>
                    <?php foreach($values['terms'] as $term): ?>
                        <p><?= $term ?></p>
                    <?php endforeach; ?>

                    <?php $url = $values['url_graph_data_terms'];?>
                    <h2 style="font-family: 'Helvetica', sans-serif;">Totales por terminos</h2>
                    <br><br>
                    <div class="chart">
                        <img src="<?= $url ?>" alt="Static Chart"/>
                    </div>
            <?php endforeach; ?> 
           </div>
       </div>
        <!-- end by resource-->
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

   
</body>
</html>