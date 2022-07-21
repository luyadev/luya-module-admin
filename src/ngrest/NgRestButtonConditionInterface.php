<?php

namespace luya\admin\ngrest;

/**
 * Ngrest Button Condition Interface
 *
 * @author Rochdi Bazine <rochdi80tn@gmail.com>
 * @since 4.0.0
 */
interface NgRestButtonConditionInterface
{
    /**
     * Setter method for the button ng-show condition
     * @param string $condition
     * @since 4.0.0
     */
    public function setCondition($condition);

    /**
     * Get the button condition or empty of not set
     * @since 4.0.0
     */
    public function getCondition();
}
