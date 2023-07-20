<?php

namespace luya\admin\models;

use luya\admin\Module;
use yii\base\Model;

/**
 * User change Password model.
 *
 * @property \luya\admin\models\User $user
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.0
 */
class UserChangePassword extends Model
{
    public $oldpass;
    public $newpass;
    public $newpassrepeat;

    private $_user;

    /**
     * Setter method for user.
     *
     * @param User $user
     */
    public function setUser(User $user)
    {
        $this->_user = $user;
    }

    /**
     * Getter method for user.
     *
     * @return \luya\admin\models\User
     */
    public function getUser()
    {
        return $this->_user;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['oldpass', 'newpass', 'newpassrepeat'], 'required'],
            [['newpass'], 'string', 'min' => '8'],
            [['newpass'], 'compare', 'compareAttribute' => 'newpassrepeat'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'oldpass' => Module::t('model_user_oldpassword'),
            'newpassrepeat' => Module::t('aws_changepassword_new_pass_retry'),
            'newpass' => Module::t('aws_changepassword_new_pass'),
        ];
    }

    /**
     * Checks if the password is valid and stores the new password.
     */
    public function checkAndStore()
    {
        if (!$this->user->validatePassword($this->oldpass)) {
            return $this->addError('oldpass', 'The previous old password is invalid.');
        }

        if (!$this->user->changePassword($this->newpass)) {
            return $this->addErrors($this->user->getErrors());
        }
    }
}
