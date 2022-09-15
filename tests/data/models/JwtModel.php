<?php

namespace luya\admin\tests\data\models;

use Lcobucci\JWT\Token\Plain;
use luya\admin\base\JwtIdentityInterface;
use luya\admin\models\ApiUser;
use luya\testsuite\fixtures\NgRestModelFixture;
use yii\base\Model;

class JwtModel extends Model implements JwtIdentityInterface
{
    public $id;
    public $firstname;
    public $lastname;

    public static function loginByJwtToken(Plain $token)
    {
        if ($token->headers()->toString() == 'token=valid') {
            $model = new NgRestModelFixture([
                'modelClass' => ApiUser::class,
                'fixtureData' => [
                    'u1' => [
                        'id' => 1000,
                        'firstname' => 'John',
                        'lastname' => 'Doe',
                        'email' => 'john@example.com',
                        'is_deleted' => 0,
                        'is_api_user' => true,
                        'api_last_activity' => time(),
                        'auth_token' => 'TestAuthToken',
                    ]
                ]
            ]);

            $x = $model->getModel('u1');

            $s = new self();
            $s->attributes = $x->toArray();
            return $s;
        }

        return false;
    }

    public function getId()
    {
        return 1;
    }
}
