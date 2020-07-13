<?php
namespace app\components;
use yii\validators\Validator;

class ProductsValidator extends Validator{
    

    public function validateValue($value)
    {
        return false;
    }

    public function clientValidateAttribute($model, $attribute, $view){
        $url = \yii\helpers\Url::to(['/products/default/check-products']);
        if($model->isNewRecord){
            return <<<JS
var message = "El Producto " + value +"  ya se encuentra registrado ";        
deferred.push($.get("$url", {value: value}).done(function(data) {
            if ('' !== data) {
                messages.push(message);
            }
        }));
JS;

        }
    }

}