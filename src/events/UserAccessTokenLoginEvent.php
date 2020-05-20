<?php

namespace luya\admin\events;

use luya\admin\models\User;
use yii\base\Event;

/**
 * @property User $user
 */
class UserAccessTokenLoginEvent extends Event
{
    public $tokenType;
    
    private $_user;

    public function setUser(User $user)
    {
        $this->_user = $user;
    }

    public function getUser()
    {
        return $this->_user;
    }
}