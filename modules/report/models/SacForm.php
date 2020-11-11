<?php

namespace app\modules\report\models;
use Yii;
use yii\base\Model;

/**
 * SacForm is the model behind the Sac form.
 */
class SacForm extends Section
{
    public $head_title;
    public $mencion_by_categories;
    public $mencion_by_categories_checked;
    // public $titles = [
    //     "SAC SNS AGOSTO 2020",
    //     "MENSAJES RECIBIDOS POR RED SOCIAL 2020",
    //     "COMENTARIOS PÚBLICOS",
    //     "MENSAJES PRIVADOS",
    //     "CLASIFICACIÓN DEL TIPO DE MENSAJE"

    // ];
    public $titles = [];

    public $tables_coordinates = [];   
    public $graph_coordinates = [];   

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            // name, email, subject and body are required
            [['head_title','titles','tables_coordinates','graph_coordinates'], 'required'],
        ];
    }

    /**
     * @return array customized attribute labels
     */
    public function attributeLabels()
    {
        return [
            'mencion_by_categories' => 'MENCIONES POR CATEGORÍA',
            'head_title' => 'Titulo de Cabecera',
            'titles' => 'Titulo',
            'tables_coordinates' => 'Rango Tabla',
            'graph_coordinates' => 'Hoja Graficos'
        ];
    }

    
}
