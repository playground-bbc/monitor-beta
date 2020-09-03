<?php

namespace app\modules\user\models;

use Yii;
use yii\base\Model;


/**
 * ContactForm is the model behind the contact form.
 */
class UserEditForm extends Model
{
    public $username;
    public $email;
    public $password;
    public $password_repeat;

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['username', 'email','password'], 'required'],
            ['email', 'email'],
            ['password', 'string', 'min' => 6],
            ['password_repeat', 'compare', 'compareAttribute'=>'password', 'skipOnEmpty' => false, 'message'=>"Passwords don't match"],
        ];
    }

    /**
     * @return array customized attribute labels
     */
    public function attributeLabels()
    {
        return [
            'username' => 'Nombre de Usuario',
            'email' => 'Correo electrónico',
            'password' => 'Contraseña'
        ];
    }

}
