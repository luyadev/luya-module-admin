<?php

namespace luya\admin\ngrest\plugins;

use luya\admin\ngrest\base\Plugin;

/**
 * Sortable Plugin.
 *
 * Generates an arrow down/up ability to click direct in the CRUD list.
 *
 * When using the Sortable Plugin make sure to use the {{luya\admin\traits\SortableTrait}} within the Model. This will
 * ensure the default ordering for your fields and disabled the sorting inside the grid.
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.0
 */
class Sortable extends Plugin
{
    /**
     * @inheritdoc
     */
    public function renderList($id, $ngModel)
    {
        return [
            $this->createTag('i', 'keyboard_arrow_up', ['ng-init' => '$first ? changeOrder(\''.$this->name.'\', \'+\') : null', 'ng-click' => 'sortableUp($index, item, \''.$this->name.'\')', 'ng-class' => '{\'sortable-up-first\' : $first && pager.currentPage == 1}', 'class' => 'material-icons btn btn-outline-secondary btn-symbol']),
            $this->createTag('i', 'keyboard_arrow_down', ['ng-click' => 'sortableDown($index, item, \''.$this->name.'\')', 'ng-class' => '{\'sortable-up-last\' : $last && pager.currentPage == pager.pageCount}', 'class' => 'material-icons btn btn-outline-secondary btn-symbol']),
            $this->createTag('span', '{{'.$ngModel.'}}', ['class' => 'badge badge-light'])
        ];
    }

    /**
     * @inheritdoc
     */
    public function renderCreate($id, $ngModel)
    {
        return $this->createFormTag('zaa-number', $id, $ngModel, ['min' => 1]);
    }

    /**
     * @inheritdoc
     */
    public function renderUpdate($id, $ngModel)
    {
        return $this->renderCreate($id, $ngModel);
    }
}
