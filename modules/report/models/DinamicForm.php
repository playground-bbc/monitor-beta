<?php

namespace app\modules\report\models;
use Yii;
use yii\base\Model;

/**
 * DinamicForm is the model behind the dinamic form.
 */
class DinamicForm extends Section
{
    public $head_title;
    public $title;

    public $graphic_facebook;
    public $graphic_instagram;

    public $table_facebook;
    public $table_instagram;

    public $best_publication;
    public $sentiment_by_category;
    public $message_by_product_category;
    public $case_succes_sac;

    public $sentiment_by_category_checked;
    public $message_by_product_category_checked;
    public $case_succes_sac_checked;

    const TITLE_FIXED = [
        'best_publication' => 'MEJOR PUBLICACIÓN',
        'sentiment_by_category' => 'SENTIMIENTO POR CATEGORÍA',
        'message_by_product_category' => 'MENSAJES POR PRODUCT CATEGORY',
        'case_succes_sac' => 'CASO DE ÉXITO SAC',
    ];



    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            // name, email, subject and body are required
            [['head_title','title','graphic_facebook','graphic_instagram','table_facebook','table_instagram'], 'required'],
        ];
    }

    /**
     * @return array customized attribute labels
     */
    public function attributeLabels()
    {
        return [
            'head_title' => Yii::t('app', 'Título de Portada'),
            'title' => Yii::t('app', 'Título de Cabecera'),
            'sentiment_by_category' => 'Sentimiento por categoria',
            'message_by_product_category' => 'Mensajes por producto',
            'case_succes_sac' => 'Caso de exito SAC',
            'graphic_facebook' =>'Hoja Graficos Facebook',
            'graphic_instagram' => 'Hoja Graficos Instagram'
        ];
    }

    
}
