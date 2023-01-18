<?php

namespace luya\admin\ngrest\traits;

/**
 * NgRest Crud Loader Plugin Trait
 *
 * @since 4.9.0
 */
trait CrudLoaderPluginTrait
{
    /**
     * @var boolean Whether the current active pool should be propagated to the ngrest loader. This can be a problem when you are in a pool situation
     * but the relation does not implement such a pool, therefor this can be turned off.
     * @since 4.9.0
     */
    public $crudLoaderPoolContext = true;
}
