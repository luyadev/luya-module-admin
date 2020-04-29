<?php

namespace luya\admin\apis;

/**
 * Storage Image Controller.
 * 
 * File has been created with `crud/create` command. 
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