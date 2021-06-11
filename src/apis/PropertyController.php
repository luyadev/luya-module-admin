<?php

namespace luya\admin\apis;

/**
 * Property Controller.
 *
 * @author Basil Suter <git@nadar.io>
 * @since 3.8.0
 */
class PropertyController extends \luya\admin\ngrest\base\Api
{
    /**
     * @var string The path to the model which is the provider for the rules and fields.
     */
    public $modelClass = 'luya\admin\models\Property';
}
