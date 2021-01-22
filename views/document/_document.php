<?php 
use yii\helpers\Html;

// load dates to cover
$start_date = \Yii::$app->formatter->asDatetime($model->config->start_date,'dd/MM/yyyy');
$end_date   = \Yii::$app->formatter->asDatetime($model->config->end_date,'dd-MM/yyyy');
$new_time = date("d/m", $model->config->start_date);
$now = date("h:i d/m");
$emoji = \IntlChar::chr("\u{2603}");


?>

<!DOCTYPE html>
<html lang="en">
<head>
    
</head>
<body>
    <h1 class="emoji"><?= $emoji ?></h1>
    <p>🏀🏀🏀🏀</p>
    <p>&#x1F600</p>

    <p class="emoji">🏀🏀🏀🏀</p>
    <p class="emoji">&#x1F600</p>
    <div class="container">
        <?= $this->render('_cover',[
            'model' => $model,
            'url_logo_small' => $url_logo_small,
            'url_logo' =>$url_logo,
        ]) ?>
        <!-- break to another page -->
        <div style='page-break-after:always'></div>
        <!-- end break to another page -->
        <?= $this->render('_detail_alert',[
            'model' => $model,
            'url_graph_count_sources' => (isset($resourcesSocialData['url_graph_count_sources'])) ? $resourcesSocialData['url_graph_count_sources'] : null,
            'url_graph_date_sources' => (isset($resourcesSocialData['url_graph_date_sources'])) ? $resourcesSocialData['url_graph_date_sources'] : null
            ]) ?>
        <!-- break to another page -->
        <div style='page-break-after:always'></div>
        <!-- end break to another page -->
        <?= $this->render('_terms_searched',['model' => $model]) ?>
        <!-- break to another page -->
        <div style='page-break-after:always'></div>
        <!-- end break to another page -->    
        <?php if(isset($resourcesSocialData['resources']) && count($resourcesSocialData['resources'])): ?>
            <?php $index = 0; ?> 
            <?php foreach($resourcesSocialData['resources'] as $resourceName  => $values):?>
                <?= $this->render('_resource',['resourceName' => $resourceName,'values' => $values]) ?>
                <?php $index ++; if($index < count($resourcesSocialData['resources'])):?> 
                    <!-- break to another page -->
                    <div style='page-break-after:always'></div>
                    <!-- end break to another page -->   
                <?php endif; ?>
            <?php endforeach; ?>
        <?php endif; ?>
        
        
        <h2>Emojis mas usados en las menciones</h2>
        <?php if(isset($resourcesSocialData['emojis']) && count($resourcesSocialData['emojis'])): ?>
            <table class="table table-striped">
                <thead>
                <tr>
                    <th>Emoji</th>
                    <th>Total</th>
                </tr>
                </thead>
                <tbody>
                    <?php foreach($resourcesSocialData['emojis'] as $emojiName  => $value):?>
                        <?= $this->render('_emoji',['value' => $value]) ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>    
    </div>

</body>
</html>
