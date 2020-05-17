<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

\app\assets\highchartsAsset::register($this);
\app\assets\JqcloudAsset::register($this);
\app\assets\AxiosAsset::register($this);
\app\assets\VueAsset::register($this);
\app\assets\TopicAsset::register($this);

/* @var $this yii\web\View */
/* @var $model app\modules\topic\models\MTopics */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'M Topics'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="mtopics-view" id="topics-view">

    <h1><?= Html::encode($this->title) ?></h1>
    <?= Html::hiddenInput('topicId', $model->id,['id' => 'topicId']); ?>

    <p>
        <?= Html::a(Yii::t('app', 'Update'), ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a(Yii::t('app', 'Delete'), ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => Yii::t('app', 'Are you sure you want to delete this item?'),
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => \app\helpers\TopicsHelper::getAttributesForDetailView($model),
    ]) ?>


    <cloud-view></cloud-view>
    

    

</div>
<?= $this->render('_templates-vue');  ?>
