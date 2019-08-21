<?php

namespace luya\admin\components;

use Yii;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use sizeg\jwt\Jwt as BaseJwt;
use yii\web\IdentityInterface;

/**
 * The Jwt component.
 * 
 * In addition to the {{sizeg\jwt\Jwt}} class this methods provides ability to generate a token
 * based on identity interface.
 * 
 * @author Basil Suter <basil@nadar.io>
 * @since 2.0.2
 */
class Jwt extends BaseJwt
{
    /**
     * @var integer The default expire time when using {{generateToken()}} method.
     */
    public $expireTime = 3600; // 1 hour

    /**
     * Method to generate a token from an user with identity interface.
     * 
     * Use this method to return the jwt token by a public accessable end point which validates the user credentials.
     * 
     * ```php
     * public function actionUserLogin()
     * {
     *     $model = new UserLogin();
     *     $model->attributes = Yii::$app->request->post();
     * 
     *     if ($model->validate()) {
     *         $token = Yii::$app->jwt->generateToken($model);
     * 
     *         // depending on whether you like to store the jwt token or use the uid claim to retrieve the user id.
     *         $model->updateAttributes(['jwt_token' => $token]);
     * 
     *         return $token;
     *     }
     * 
     *     throw new InvalidRequest("Unable to find and validate the givne User.");
     * }
     * ```
     *
     * @param IdentityInterface $user
     * @return string
     */
    public function generateToken(IdentityInterface $user)
    {
        // here you can put some credentials validation logic
        // so if it success we return token
        $signer = new Sha256();
        
        $token = $this->getBuilder()
            ->setIssuer(Yii::$app->request->hostInfo) // Configures the issuer (iss claim)
            ->setAudience(Yii::$app->request->hostInfo) // Configures the audience (aud claim)
            ->setId(Yii::$app->security->generatePasswordHash($user->getId()), true) // Configures the id (jti claim), replicating as a header item
            ->setIssuedAt(time()) // Configures the time that the token was issue (iat claim)
            ->setExpiration(time() + $this->expireTime) // Configures the expiration time of the token (exp claim)
            ->set('uid', $user->getId()) // Configures a new claim, called "uid"
            ->sign($signer, $this->key ) // creates a signature using [[Jwt::$key]]
            ->getToken();

        return $token->__toString();
    }
}