<?php

namespace luya\admin\base;

use Lcobucci\JWT\Token;
use yii\base\Configurable;

/**
 * The interface which integrates JWT authentification against your user.
 * 
 * @author Basil Suter <basil@nadar.io>
 * @since 2.2.0
 */
interface JwtIdentityInterface extends Configurable
{
    public static function loginByJwtToken(Token $token);
}