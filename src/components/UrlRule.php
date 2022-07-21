<?php

namespace luya\admin\components;

/**
 * Url rule for NgRest Apis.
 *
 * As ngrest apis should have the same module as identifer like `admin/api-user-lists` therefore
 * this rule is going to "fake" those admin module routes.
 *
 * The controllerMap property from the admin module is provided by {{luya\web\Bootstrap::run()}} method.
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.0
 */
class UrlRule extends \yii\rest\UrlRule
{
    /**
     * @var boolean This is used to ensure the url rule caching works with yii version 2.0.14
     * @see https://github.com/luyadev/luya-module-admin/issues/68
     */
    public $cacheFlag;

    /**
     * @inheritdoc
     */
    public $pluralize = false;
}
