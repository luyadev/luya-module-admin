<?php

namespace luya\admin\controllers;

/**
 * User Request Controller.
 *
 * File has been created with `crud/create` command.
 */
class UserRequestController extends \luya\admin\ngrest\base\Controller
{
    /**
     * @var string The path to the model which is the provider for the rules and fields.
     */
    public $modelClass = 'luya\admin\models\UserRequest';
}
