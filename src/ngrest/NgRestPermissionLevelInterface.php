<?php

namespace luya\admin\ngrest;

/**
 * Ngrest Button Permission Condition Interface
 *
 * @author Rochdi Bazine <rochdi80tn@gmail.com>
 * @since 4.0.0
 */
interface NgRestPermissionLevelInterface
{
    /**
     * Setter method for the ActiveWindow button displaying permission level
     * @param int $permissionLevel
     * @since 4.0.0
     */
    public function setPermissionLevel($permissionLevel);

    /**
     * Get the button displaying permission level or empty of not set
     * @since 4.0.0
     */
    public function getPermissionLevel();
}
