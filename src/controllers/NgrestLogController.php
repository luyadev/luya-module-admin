<?php

namespace luya\admin\controllers;

use luya\admin\Module;

/**
 * Ngrest Log Controller.
 * 
 * @author Basil Suter <git@nadar.io>
 * @since 3.2.0
 */
class NgrestLogController extends \luya\admin\ngrest\base\Controller
{
    /**
     * @var string The path to the model which is the provider for the rules and fields.
     */
    public $modelClass = 'luya\admin\models\NgrestLog';

    /**
     * {@inheritDoc}
     */
    public $clearButton = true;
    
    /**
     * {@inheritDoc}
     */
    public function getDescription()
    {
        return Module::t('ngrestlog_controller_description');
    }
}