<?php

namespace luya\admin\events;

use luya\admin\models\User;
use yii\base\Event;
use yii\base\InvalidConfigException;

/**
 * Login by access token event
 *
 * This event is raised during the {{luya\admin\models\User::findIdentityByAccessToken()}} process.
 *
 * In order to login a given user set `$event->login($user)`
 *
 * @property User $user
 *
 * @since 3.3.0
 * @author Basil Suter <git@nadar.io>
 */
class UserAccessTokenLoginEvent extends Event
{
    /**
     * @var string Contains the type of login identified by the class name.
     */
    public $type;

    /**
     * @var string  The token which is looking for an user.
     */
    public $token;

    private $_user;

    /**
     * Login a given User.
     *
     * @param User $user
     */
    public function login(User $user)
    {
        if ($user->is_deleted) {
            throw new InvalidConfigException("Unable to login the deleted user.");
        }

        $this->_user = $user;
    }

    /**
     * Get User
     *
     * @return User
     */
    public function getUser()
    {
        return $this->_user;
    }
}
