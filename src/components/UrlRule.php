<?php

namespace luya\admin\components;

use yii;

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
    
    /**
     * @inheritdoc
     */
    /*
    public function init()
    {
        // get all controller mappings
        // the key is the alias the value the path to the api class.
        $map = Yii::$app->getModule('admin')->controllerMap;

        // if there are rules we have to set them into the url rules controller property.
        if (count($map) > 0) {
            // assign the url rule from the admins controller map
            foreach ($map as $alias => $className) {
                // effect controller roule would be: `admin/api-admin-user` as its registered in the controller map of the admin module`.
                $this->controller[] = sprintf('%s/%s', 'admin', $alias);
            }

            return parent::init();
        }
        
        // if there is no controller from controller map we have to fake
        // an empty controller property otherwise the url rule will generate
        // an error.
        $this->controller = [];
    }
    */
}
