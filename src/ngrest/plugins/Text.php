<?php

namespace luya\admin\ngrest\plugins;

use luya\admin\ngrest\base\Plugin;
use luya\helpers\Html as HtmlHelper;

/**
 * Create a text input select for a given field.
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.0
 */
class Text extends Plugin
{
    /**
     * @var string Define a HTML placeholder attribute.
     */
    public $placeholder;
    
    /**
     * @var boolean Whether the value should be encoded after find by {{luya\helpers\Html::encode()}} or not.
     */
    public $encoding = true;
    
    /**
     * @var array An array with options can be passed to the createListTag.
     */
    public $listOptions = [];

    /**
     * @inheritdoc
     */
    public function renderList($id, $ngModel)
    {
        return $this->createListTag($ngModel, $this->listOptions);
    }

    /**
     * @inheritdoc
     */
    public function renderCreate($id, $ngModel)
    {
        return $this->createFormTag('zaa-text', $id, $ngModel, ['placeholder' => $this->placeholder]);
    }

    /**
     * @inheritdoc
     */
    public function renderUpdate($id, $ngModel)
    {
        return $this->renderCreate($id, $ngModel);
    }
    
    /**
     * @inheritdoc
     */
    public function onAfterFind($event)
    {
        if ($this->encoding) {
            $this->writeAttribute($event, HtmlHelper::encode($event->sender->getAttribute($this->name)));
        }
    }
    
    /**
     * @inheritdoc
     */
    public function onAssign($event)
    {
        if (!$this->i18n && $this->encoding && $this->isAttributeWriteable($event)) {
            $this->writeAttribute($event, HtmlHelper::encode($event->sender->getAttribute($this->name)));
        }
    }
}
