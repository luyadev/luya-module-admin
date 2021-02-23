<?php

namespace luya\admin\aws;

/**
 * Change Password Active Window Interface.
 *
 * Each {{luya\admin\aws\ChangePasswordActiveWindow}} must implemented this interface
 *
 * ```php
 * class User extends \luya\admin\ngrest\base\NgRestModel implements \luya\admin\aws\ChangePasswordInterface
 * {
 *    public function changePassword($newPassword)
 *    {
 *        return $this->updateAttributes(['password' => Yii::$app->security->generatePasswordHash($newPassword)]);
 *    }
 * }
 * ```
 *
 * The changePassword method must return whether the password change was successful or not.
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.0
 */
interface ChangePasswordInterface
{
    /**
     * The method which is going to change the password on the current model.
     *
     * The implementation of this must make sure if the $newPassword and $newPasswordRepetition are equals!
     *
     * @param string $newPassword The new password (as plain input) which must be encoded an set.
     * @return boolean Whether password change was successful or not.
     */
    public function changePassword($newPassword);
}
