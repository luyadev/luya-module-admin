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
     * {@inheritDoc}
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
