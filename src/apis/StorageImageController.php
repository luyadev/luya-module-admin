<?php

namespace luya\admin\apis;

/**
 * Storage Image Controller.
 *
 * @author Basil Suter <git@nadar.io>
 * @since 3.2.0
 */
class StorageImageController extends \luya\admin\ngrest\base\Api
{
    /**
     * @var string The path to the model which is the provider for the rules and fields.
     */
    public $modelClass = 'luya\admin\models\StorageImage';

    /**
     * {@inheritDoc}
     */
    public function prepareListQuery()
    {
        return parent::prepareListQuery()->with(['file', 'filter']);
    }
}
