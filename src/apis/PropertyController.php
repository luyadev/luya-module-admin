<?php

namespace luya\admin\apis;

/**
 * Property Controller.
 * 
 * File has been created with `crud/create` command. 
 */
class PropertyController extends \luya\admin\ngrest\base\Api
{
    /**
     * @var string The path to the model which is the provider for the rules and fields.
     */
    public $modelClass = 'luya\admin\models\Property';
}