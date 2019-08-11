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
    /**
     * Ensure the user login by token.
     *
     * The user id to get the given user is commonly stored as `uid` claim. Therefore
     * in order to get the user id use getClaim:
     * 
     * ```php
     * public staitc function loginByJwtToken(Token $token)
     * {
     *     // get the user id
     *     $userId = $token->getClaim('uid');
     * 
     *     return User::findOne($userId);
     * }
     * ```
     * 
     * Depending on your setup you also might to store the jwt token while authentication. Then you can
     * retrieve the jwt token by calling `__toString()` method.
     *
     * ```php
     * public staitc function loginByJwtToken(Token $token)
     * {
     *     // get the user id
     *     $userId = $token->getClaim('uid');
     *     // get the jwt token
     *     $jwtToken = $token->__toString();
     * 
     *     return User::findOne(['id' => $userId, 'jwt_access_token' => $jwtToken]);
     * }
     * ```
     *  
     * @see Discussion regarding storing the jwt token: https://stackoverflow.com/a/42765870/4611030  
     * @param Token $token
     * @return boolean
     */
    public static function loginByJwtToken(Token $token);
}