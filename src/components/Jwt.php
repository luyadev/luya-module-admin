<?php

namespace luya\admin\components;

use bizley\jwt\Jwt as JwtJwt;
use Lcobucci\JWT\Validation\Constraint\IssuedBy;
use Lcobucci\JWT\Validation\Constraint\PermittedFor;
use luya\admin\base\JwtIdentityInterface;
use luya\admin\models\ApiUser;
use luya\helpers\ObjectHelper;
use Yii;
use yii\base\InvalidConfigException;

/**
 * The Jwt component.
 *
 * To successful configure the jwt component {{Jwt::$key}}, {{Jwt::$apiUserEmail}} and {{Jwt::$identityClass}} can not be null.
 *
 * ```php
 * 'components' => [
 *     'jwt' => [
 *          'class' => 'luya\admin\components\Jwt',
 *          'key' => 'MySecretJwtKey',
 *          'apiUserEmail' => 'jwtapiuser@luya.io',
 *          'identityClass' => 'app\modules\myadminmodule\models\User',
 *     ],
 * ],
 * ```
 *
 * An example of create a custom endpoint to retrieve the JWT auth user data:
 *
 * ```php
 * class MeController extends RestController
 * {
 *     public $authOptional = ['login'];
 *
 *     public function actionLogin()
 *     {
 *         $model = new FrontendUser();
 *         // assuming you have a logic which ensures the user data (mail, pw)
 *         if ($model->login($_POST)) {
 *              $token = Yii::$app->jwt->generateToken($model);
 *              $model->jwt_token = $token;
 *              $model->update();
 *
 *              return $this->asJson([
 *                  'token' => $token,
 *              ]);
 *          }
 *     }
 *
 *     public function actionData()
 *     {
 *          // the authenticated user identity.
 *          $model = Yii::$app->jwt->identity;
 *          return $model;
 *      }
 * }
 * ```
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 2.0.2
 */
class Jwt extends JwtJwt
{
    /**
     * @var JwtIdentityInterface If an authentification trough jwt token happnes, this variable holds the jwt user identity.
     */
    public $identity;

    /**
     * @var string The email of the LUYA Admin API User which will taken to proxy the jwt requests. This is required
     * as the permissions are set for api users.
     */
    public $apiUserEmail;

    /**
     * @var string A path or configurable array definition to the class which implements {{luya\admin\base\JwtIdentityInterface}}.
     */
    public $identityClass;

    /**
     * @var integer The default expire time when using {{generateToken()}} method.
     */
    public $expireTime = 3600; // 1 hour

    /**
     * @var string The issuer (iss claim). If not defined the Yii::$app->request->hostInfo will be taken.
     * @since 2.3.0
     */
    public $issuer;

    /**
     * @var string The audience (aud claim). If not defined the Yii::$app->request->hostInfo will be taken.
     * @since 2.3.0
     */
    public $audience;

    public $key;

    /**
     * {@inheritDoc}
     */
    public function init(): void
    {
        $this->signingKey = $this->key;
        $this->signer = self::HS256;
        if (!$this->apiUserEmail || !$this->identityClass || !$this->key) {
            throw new InvalidConfigException("The attributes apiUserEmail, identityClass and key can not be empty.");
        }
        $this->validationConstraints = [
            new PermittedFor($this->getAudience()),
            new IssuedBy($this->getIssuer()),
        ];
        parent::init();
    }

    private function getAudience()
    {
        return $this->audience ?: Yii::$app->request->hostInfo;
    }

    private function getIssuer()
    {
        return $this->issuer ?: Yii::$app->request->hostInfo;
    }

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
     *         // depending on whether you like to store the jwt token or use the uid claim to retrieve the user id.
     *         $model->updateAttributes(['jwt_token' => $token]);
     *         return $token;
     *     }
     *
     *     throw new InvalidRequest("Unable to find and validate the givne User.");
     * }
     * ```
     *
     * @param JwtIdentityInterface $user The user to generate the access token from.
     * @return string
     */
    public function generateToken(JwtIdentityInterface $user)
    {
        $now = new \DateTimeImmutable();
        $token = $this->getBuilder()
            ->issuedBy($this->getIssuer())
            ->permittedFor($this->getAudience())
            ->identifiedBy($user->getId())
            ->withClaim('uid', $user->getId())
            ->issuedAt($now)
            ->expiresAt($now->modify('+'.$this->expireTime . ' minutes'))
            ->getToken(
                $this->getConfiguration()->signer(),
                $this->getConfiguration()->signingKey()
            );

        return $token->toString();
    }

    /**
     * Authenticate a user from a given user
     *
     * @param string $token
     * @return null|true
     */
    public function authenticateUser($token)
    {
        $modelClass = Yii::createObject($this->identityClass);

        if (!ObjectHelper::isInstanceOf($modelClass, JwtIdentityInterface::class, false)) {
            throw new InvalidConfigException("The identityClass must implement the JwtIdentityInterface interface.");
        }

        $auth = $modelClass::loginByJwtToken($token);

        // validation was success, now return the API user in terms of permissions:
        if ($auth && ObjectHelper::isInstanceOf($auth, JwtIdentityInterface::class, false)) {
            // login the api user to the adminuser component.
            $user = ApiUser::find()->andWhere(['email' => $this->apiUserEmail, 'is_api_user' => true])->one();

            if (!$user) {
                throw new InvalidConfigException("The jwt api user could not be found. Ensure `apiUserEmail` with value `{$this->apiUserEmail}` is configured property.");
            }

            $this->identity = $auth;

            return Yii::$app->adminuser->loginByAccessToken($user->auth_token, 'bizley\jwt\JwtHttpBearerAuth');
        }

        return null;
    }
}
