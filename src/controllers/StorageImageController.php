<?php

namespace luya\admin\controllers;

use luya\admin\Module;

/**
 * Storage Image Controller.
 *
 * @author Basil Suter <git@nadar.io>
 * @since 3.2.0
 */
class StorageImageController extends \luya\admin\ngrest\base\Controller
{
    /**
     * {@inheritDoc}
     */
    public $modelClass = 'luya\admin\models\StorageImage';

    /**
     * {@inheritDoc}
     */
    public function getDescription()
    {
        return Module::t('storageimage_controller_description');
    }
}
