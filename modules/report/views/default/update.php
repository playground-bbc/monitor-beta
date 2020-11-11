<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\modules\presentation\models\Presentation */

$this->title = Yii::t('app', 'Update Presentation: {name}', [
    'name' => $model->name,
]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Presentations'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Update');
?>
<div class="presentation-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
        'modelsSection' => $modelsSection,
        'modelsPage' => $modelsPage,
        'modelOverViewSection' => $modelOverViewSection,
        'modelOverViewPage' => $modelOverViewPage,
        'modelOverViewPageElement' => $modelOverViewPageElement,
        'modelsSectionDinamic' => $modelsSectionDinamic,
        'sacForm' => $sacForm,
        'sheetRanges' => $sheetRanges
    ]) ?>

</div>
