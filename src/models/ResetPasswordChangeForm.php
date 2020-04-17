<?php

namespace luya\admin\models;

use yii\base\Model;

class ResetPasswordChangeForm extends Model
{
    public $password;

    public function rules()
    {
        return [
            [['password'], 'required'],
            [['password'], 'string', 'min' => '8'],
        ];
    }

    public function formName()
    {
        return 'change';
    }
}
