<?php

namespace luya\admin\ngrest\plugins;

use luya\admin\ngrest\base\Plugin;

/**
 * Create a WYSIWYG input for a given field.
 *
 *
 * ```php
 * ['mytext' => ['wysiwyg']]
 * ```
 *
 * @author David-Julian Buch <david@web-premiere.fr>
 * @since 1.2.3
 */
class Wysiwyg extends Plugin
{
    /**
     * @inheritdoc
     */
    public function renderList($id, $ngModel)
    {
        return $this->createListTag($ngModel);
    }

    /**
     * @inheritdoc
     */
    public function renderCreate($id, $ngModel)
    {
        return $this->createFormTag('zaa-wysiwyg', $id, $ngModel);
    }

    /**
     * @inheritdoc
     */
    public function renderUpdate($id, $ngModel)
    {
        return $this->renderCreate($id, $ngModel);
    }
}
