<?php

namespace luya\admin\ngrest\plugins;

use luya\admin\ngrest\base\Plugin;

/**
 * Renders Angular in List View.
 * 
 * If you want to directly apply angular code within forms, or the list you can use this plugin.
 * 
 * Example usage to dump all items in the current row of grid list
 * 
 * ```php
 * 'dump' => ['angular'],
 * ```
 * 
 * Assuming `getDump()` returns
 * 
 * ```php
 * public funtion getDump()
 * {
 *     return '{{ item | json }}';
 * }
 * ``` 
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.2.2
 */
class Angular extends Plugin
{
    /**
     * @inheritdoc
     */
    public function renderList($id, $ngModel)
    {
        return $this->createTag('div', $this->renderContext->getModel()->{$this->name});
    }

    /**
     * @inheritdoc
     */
    public function renderCreate($id, $ngModel)
    {
        return $this->renderList($id, $ngModel);
    }

    /**
     * @inheritdoc
     */
    public function renderUpdate($id, $ngModel)
    {
        return $this->renderList($id, $ngModel);
    }
}
