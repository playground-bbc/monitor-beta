<?php

namespace app\modules\report\models;
use Yii;
use yii\base\Model;

/**
 * SheetForm is the model behind the Presentation.
 */
class SheetForm extends Model
{

    public $url;
    
    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['url'], 'required'],
            [['url'], 'url'],
            [['url'], 'checkDomain','skipOnEmpty' => false],
        ];
    }

    public function checkDomain($attribute,$params)
    {
        $sGoogleDomain = \app\modules\report\helpers\SheetHelper::getDomain($this->url);

        // Check if the domain
        if($sGoogleDomain != 'google.com'){
            //  add the error
            $this->addError('url', 'No es una Url valida de Google Sheet Document');
        }
        return true;
        
    }

    /**
     * @return array customized attribute labels
     */
    public function attributeLabels()
    {
        return [
            'url' => 'Url del Google SpreedSheet'
        ];
    }

    
}
