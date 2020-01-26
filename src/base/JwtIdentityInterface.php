<?php

namespace luya\admin\base;

use Lcobucci\JWT\Token;
use yii\base\Configurable;

/**
 * The interface which integrates JWT authentification against users.
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
     *     return User::find()->where(['id' => $userId, 'is_active' => true]);
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
     * Return false if no user is found or login is incorrect.
     *
     * @see Discussion regarding storing the jwt token: https://stackoverflow.com/a/42765870/4611030
     * @param Token $token
     * @return self|boolean Return the user object which implements JwtIdentityInterface or false if not found and login is invalid.
     */
    public static function loginByJwtToken(Token $token);

    /**
     * Returns an ID that can uniquely identify a user identity.
     * @return string|int an ID that uniquely identifies a user identity.
     */
    public function getId();
}
