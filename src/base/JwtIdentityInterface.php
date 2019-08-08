<?php

namespace luya\admin\base;

/**
 * The interface which integrates JWT authentification against your user.
 * 
 * @author Basil Suter <basil@nadar.io>
 * @since 2.2.0
 */
interface JwtIdentityInterface
{
    public static function loginByJwtToken($token);
}