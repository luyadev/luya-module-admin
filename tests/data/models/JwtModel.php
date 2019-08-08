<?php

namespace luya\admin\tests\data\models;

use yii\base\Model;
use luya\admin\base\JwtIdentityInterface;

class JwtModel extends Model implements JwtIdentityInterface
{
    public static function loginByJwtToken($token)
    {
        return $token;
    }
}