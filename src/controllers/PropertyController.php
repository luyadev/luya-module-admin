<?php

namespace luya\admin\controllers;

use luya\admin\Module;

/**
 * Property Controller.
 * 
 * File has been created with `crud/create` command. 
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