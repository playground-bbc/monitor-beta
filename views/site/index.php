<?php

/* @var $this yii\web\View */

$this->title = \Yii::$app->name;
?>
<div class="site-index">
    <div class="body-content">

        <div class="row">
            <?= app\widgets\insights\InsightsWidget::widget() ?>
        </div>

    </div>
</div>
