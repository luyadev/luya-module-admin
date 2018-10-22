<?php

namespace luya\admin\ngrest\plugins;

use luya\admin\ngrest\base\Plugin;
use luya\helpers\Html as HtmlHelper;

/**
 * Create a text input select for a given field.
 *
 * @author Bennet Klarhoelter <boehsermoe@me.com>
 * @since 1.2.3
 */
class Injector extends Plugin
{
    /**
     * @var string
     */
    public $dir;
    
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
        return $this->createFormTag('zaa-injector', $id, $ngModel, ['dir' => 'data.create.' . $this->dir, 'options' => null]);
    }

    /**
     * @inheritdoc
     */
    public function renderUpdate($id, $ngModel)
    {
        return $this->createFormTag('zaa-injector', $id, $ngModel, ['dir' => 'data.update.' . $this->dir, 'options' => null]);
    }
}
