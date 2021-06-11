<?php

namespace luya\admin\behaviors;

use Yii;
use yii\behaviors\BlameableBehavior as BehaviorsBlameableBehavior;
use yii\web\Application;

/**
 * Admin User Component Blameable Behavior.
 *
 * Uses the LUYA admin user (adminuser) component id if available.
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 3.0.0
 */
class BlameableBehavior extends BehaviorsBlameableBehavior
{
    protected function getValue($event)
    {
        if ($this->value === null && Yii::$app instanceof Application && Yii::$app->has('adminuser')) {
            $userId = Yii::$app->get('adminuser')->id;
            if ($userId === null) {
                return $this->getDefaultValue($event);
            }
            return $userId;
        } elseif ($this->value === null) {
            return $this->getDefaultValue($event);
        }

        return parent::getValue($event);
    }
}
