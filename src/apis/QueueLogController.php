<?php

namespace luya\admin\apis;

/**
 * Queue Log Controller.
 * 
 * File has been created with `crud/create` command. 
 */
class QueueLogController extends \luya\admin\ngrest\base\Api
{
    /**
     * @var string The path to the model which is the provider for the rules and fields.
     */
    public $modelClass = 'luya\admin\models\QueueLog';
}