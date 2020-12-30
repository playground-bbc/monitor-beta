<?php 
  use yii\helpers\Html;
?>
<tr data-key="<?= $index ?>">
    <td><?= Html::encode($model['recurso']) ?></td>

    <td><?= Html::encode($model['term_searched']) ?></td>

    <td><?= Html::encode($model['name']) ?></td>

    <td><?= Html::encode($model['screen_name']) ?></td>

    <td><?= Html::encode(substr($model['message_markup'],0,20))  ?></td>

    <td><?php
        $url = '-';
        if(!is_null($model['url'])){
            $url = \yii\helpers\Html::a('link',$model['url'],['target'=>'_blank', 'data-pjax'=>"0"]);  
        }
        echo $url; ?></td>
</tr>