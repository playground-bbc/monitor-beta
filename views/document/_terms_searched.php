<?php 
use yii\helpers\Html;

$html = Html::ul($model->products , ['item' => function($product, $index) {
    return Html::tag(
        'li',
        $product
    // ['class' => 'post']
    );
},'class' => 'list-inline']);
?>
<div class="row">
    <div class="col-md-12">
        <!-- show  terms searched -->
        <h2 style="font-family: 'Helvetica', sans-serif;"><?= $model->name ?></h2>
        <h1 style="font-family: 'Helvetica', sans-serif;">Escucha</h1>

        <div>
        <?= $html ?>
        <!-- end show  terms searched -->
        </div>
    </div>
</div>