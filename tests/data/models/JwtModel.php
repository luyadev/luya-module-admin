<?php

namespace luya\admin\tests\data\models;

use yii\base\Model;
use luya\admin\base\JwtIdentityInterface;
use Lcobucci\JWT\Token;

class JwtModel extends Model implements JwtIdentityInterface
{
    public static function loginByJwtToken(Token $token)
    {
        if ($token->getPayload() == '1.1') {
            return true;
        }

        return false;
    }
}