<?php

namespace luya\admin\ngrest\plugins;

use luya\admin\helpers\I18n;
use luya\admin\ngrest\base\Plugin;

/**
 * Create a list element with self adding text inputs and responses as array.
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.0
 */
class TextArray extends Plugin
{
    /**
     * {@inheritDoc}
     */
    public $i18nEmptyValue = [];

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
        return $this->createFormTag('zaa-list-array', $id, $ngModel);
    }

    /**
     * {@inheritDoc}
     */
    public function renderUpdate($id, $ngModel)
    {
        return $this->renderCreate($id, $ngModel);
    }

    /**
     * Transform the array into the internal array notation with a key named `value`.
     *
     * @param mixed $listArrayValue
     * @return array
     */
    private function transformList($listArrayValue)
    {
        if (empty($listArrayValue)) {
            return [];
        }

        $data = [];
        foreach ($listArrayValue as $item) {
            if (isset($item['value'])) {
                $data[] = $item['value'];
            }
        }
        return $data;
    }

    /**
     * {@inheritDoc}
     */
    public function onBeforeSave($event)
    {
        // if its not i18n casted field we have to serialize the file array as json and abort further event excution.
        if (!$this->i18n) {
            $this->writeAttribute($event, I18n::encode($event->sender->getAttribute($this->name)));
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
    public function onBeforeFind($event)
    {
        if (!$this->i18n) {
            $this->writeAttribute($event, $this->transformList($this->jsonDecode($event->sender->getAttribute($this->name))));
            return false;
        }

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function onAfterFind($event)
    {
        $this->writeAttribute($event, $this->transformList($event->sender->getAttribute($this->name)));

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function onListFind($event)
    {
        $this->writeAttribute($event, implode(", ", $this->transformList($this->jsonDecode($event->sender->getAttribute($this->name)))));
    }
}
