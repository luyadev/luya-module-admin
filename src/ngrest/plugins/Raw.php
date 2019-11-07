<?php

namespace luya\admin\ngrest\plugins;

use luya\admin\ngrest\base\Plugin;

/**
 * Renders RAW input
 *
 * Renders the RAW input as textarea and wont change data when loading/editing/saving.
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 2.3.0
 */
class Raw extends Plugin
{
    /**
     * @inheritdoc
     */
    public function renderList($id, $ngModel)
    {
        return $this->createTag('div', null, ['ng-bind-html' => $ngModel . ' | trustAsUnsafe']);
    }

    /**
     * @inheritdoc
     */
    public function renderCreate($id, $ngModel)
    {
        return $this->createFormTag('zaa-textarea', $id, $ngModel);
    }

    /**
     * @inheritdoc
     */
    public function renderUpdate($id, $ngModel)
    {
        return $this->renderCreate($id, $ngModel);
    }
}
