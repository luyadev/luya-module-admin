<?php

namespace luya\admin\openapi\events;

use luya\admin\openapi\specs\BaseSpecs;
use yii\base\Event;

/**
 * Path Paramters Event
 *
 * This event is triggered when the paramters for a PathItem are generated.
 *
 * The main intention is to have an option to
 *
 * + add globalised params for every action, for example `_format`.
 * + Remove specific params by key, assuming this is not used or should be replaced by a more accurate specific text.
 * + Replace a specific key by new object
 *
 * @author Basil Suter <git@nadar.io>
 * @since 3.5.0
 */
class PathParametersEvent extends Event
{
    /**
     * @var array The array which holds the params. The params can be identified by an unique key in order to delete, override or push new items
     * into the array.
     */
    public $params = [];

    /**
     * @var string The controller class for retrieving param infos.
     */
    public $controllerClass;

    /**
     * @var string The action class for retrieving param infos.
     */
    public $actionClass;

    /**
     * @var string The context class which is taking into account for retrieve the param data.
     */
    public $contextClass;

    /**
     * @var string The verb name context of the current context
     */
    public $verbName;

    /**
     * @var BaseSpecs The base spec class which triggers the event.
     */
    public $sender;
}
