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
            [['email'], 'validateEmail']
        ];
    }

    public function formName()
    {
        return 'reset';
    }

    public function validateEmail($attribute, $params, $validator) {
        $this->addError($attribute, 'asdfsdf');
    }
}