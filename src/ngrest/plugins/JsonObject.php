<?php

namespace luya\admin\ngrest\plugins;

use luya\admin\ngrest\base\Plugin;
use luya\helpers\Json;

/**
 * Generate a Key Value Object.
 *
 * The plugin internally stores a json object with key and value, the JsonObject plugin will transform
 * this input into an associative array with key and value.
 *
 * Example output when using JsonObject
 *
 * ```php
 * [
 *     'foo' => 'bar',
 *     'baz' => 'foo',
 * ]
 * ```
 *
 * While the keys and the value can be insereted from the admin UI.
 *
 * @author Basil Suter <git@nadar.io>
 * @since 3.9.0
 */
class JsonObject extends Plugin
{
    /**
     * {@inheritDoc}
     */
    public function renderList($id, $ngModel)
    {
        return $this->createListTag($ngModel);
    }

    /**
     * {@inheritDoc}
     */
    public function renderCreate($id, $ngModel)
    {
        return $this->createFormTag('zaa-json-object', $id, $ngModel);
    }

    /**
     * {@inheritDoc}
     */
    public function renderUpdate($id, $ngModel)
    {
        return $this->renderCreate($id, $ngModel);
    }

    /**
     * {@inheritDoc}
     */
    public function onBeforeSave($event)
    {
        if (!$this->i18n) {
            $this->writeAttribute($event, Json::encode($event->sender->getAttribute($this->name)));
            return false;
        }

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function onBeforeExpandFind($event)
    {
        if (!$this->i18n) {
            $this->writeAttribute($event, $this->jsonDecode($event->sender->getAttribute($this->name)));
            return false;
        }

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function onAfterFind($event)
    {
        $this->writeAttribute($event, Json::decode($event->sender->getAttribute($this->name)));

        return true;
    }
}
