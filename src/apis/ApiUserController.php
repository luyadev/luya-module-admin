<?php

namespace luya\admin\apis;

use luya\admin\ngrest\base\Api;

/**
 * User API, provides ability to manager and list all administration users.
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.1.0
 */
class ApiUserController extends Api
{
    /**
     * @var string Path to the user model class.
     */
    public $modelClass = 'luya\admin\models\ApiUser';
}
