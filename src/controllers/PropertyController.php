<?php

namespace luya\admin\controllers;

use luya\admin\Module;

/**
 * Property Controller.
 *
 * @author Basil Suter <git@nadar.io>
 * @since 3.8.0
 */
class PropertyController extends \luya\admin\ngrest\base\Controller
{
    /**
     * @var string The path to the model which is the provider for the rules and fields.
     */
    public $modelClass = 'luya\admin\models\Property';

    /**
     * {@inheritDoc}
     */
    public function getDescription()
    {
        return Module::t('property_controller_description');
    }
}
