<?php

namespace luya\admin\apis;

use luya\admin\ngrest\base\Api;

/**
 * Logger Api to list the logger model data.
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.0
 */
class LoggerController extends Api
{
    /**
     * {@inheritDoc}
     */
    public $modelClass = 'luya\admin\models\Logger';

    /**
     * {@inheritDoc}
     */
    public $truncateAction = true;
}
