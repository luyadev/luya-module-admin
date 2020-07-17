<?php

namespace luya\admin\controllers;

use luya\admin\Module;

/**
 * Config Controller.
 *
 * File has been created with `crud/create` command on LUYA version 1.0.0.
 */
class ConfigController extends \luya\admin\ngrest\base\Controller
{
    /**
     * {@inheritDoc}
     */
    public $modelClass = 'luya\admin\models\Config';

    /**
     * {@inheritDoc}
     */
    public function getDescription()
    {
        return Module::t('config_controller_description');
    }
}
