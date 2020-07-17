<?php

namespace luya\admin\models;

use yii\base\Model;

class ResetPasswordForm extends Model
{
    public $email;

    public function rules()
    {
        return [
            [['email'], 'email'],
            [['email'], 'required'],
        ];
    }

    public function formName()
    {
        return 'reset';
    }
}
