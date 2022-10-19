<?php

namespace luya\admin\ngrest\base;

use luya\admin\ngrest\NgRest;
use yii\base\Behavior;
use yii\base\Event;
use yii\db\ActiveRecord;

/**
 * NgRest Event Behavior.
 *
 * This Behavior is attached to all {{luya\admin\ngrest\base\NgRestModel}} Objects.
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.0
 */
class NgRestEventBehavior extends Behavior
{
    public $plugins = [];

    /**
     * @var \luya\admin\ngrest\base\Plugin[] An array which holds all the plugin objects which are loaded after the active record has been initiliazed.
     */
    private static array $_pluginInstances = [];

    /**
     * @inheritdoc
     */
    public function events()
    {
        return [
            ActiveRecord::EVENT_INIT => 'bindPluginEvents',
        ];
    }

    /**
     * Bing all plugin Events to the corresponding Owner Object.
     *
     * @param \yii\base\Event $event
     */
    public function bindPluginEvents(Event $event)
    {
        foreach ($this->plugins as $field => $plugin) {
            $plugin = self::findPluginInstance($field, $plugin, $event->sender->tableName());
            foreach ($plugin->events() as $on => $handler) {
                $event->sender->on($on, is_string($handler) ? [$plugin, $handler] : $handler);
            }
        }
    }

    /**
     * Get the Plugin object based on the Attribute Name.
     *
     * As Behaviors works like trait its possible to access the Plugin object from Model scope:
     *
     * ```php
     * $plugin = $this->getPluginObject('attributeName');
     * ```
     *
     * @param string $attribute
     * @return \luya\admin\ngrest\base\Plugin
     * @since 2.3.0
     */
    public function getPluginObject($attribute)
    {
        return self::$_pluginInstances[$this->owner->tableName()][$attribute];
    }

    /**
     * Singleton Container for Plugin Objects.
     *
     * @param string $field
     * @param array $plugin
     * @param string $tableName
     * @return \luya\admin\ngrest\base\Plugin
     */
    private static function findPluginInstance($field, array $plugin, $tableName)
    {
        if (!isset(self::$_pluginInstances[$tableName][$field])) {
            self::$_pluginInstances[$tableName][$field] = NgRest::createPluginObject($plugin['type']['class'], $plugin['name'], $plugin['alias'], $plugin['i18n'], $plugin['type']['args']);
        }

        return self::$_pluginInstances[$tableName][$field];
    }
}
