<?php

namespace luya\admin\apis;

/**
 * Ngrest Log Controller.
 * 
 * File has been created with `crud/create` command. 
 */
class NgrestLogController extends \luya\admin\ngrest\base\Api
{
    /**
     * @var string The path to the model which is the provider for the rules and fields.
     */
    public $modelClass = 'luya\admin\models\NgrestLog';
}