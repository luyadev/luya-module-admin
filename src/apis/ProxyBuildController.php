<?php

namespace luya\admin\apis;

/**
 * Proxy Build Controller.
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.0
 */
class ProxyBuildController extends \luya\admin\ngrest\base\Api
{
    /**
     * {@inheritDoc}
     */
    public $modelClass = 'luya\admin\models\ProxyBuild';

    /**
     * {@inheritDoc}
     */
    public $truncateAction = true;
}
