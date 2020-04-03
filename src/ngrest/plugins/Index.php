<?php

namespace luya\admin\ngrest\plugins;

/**
 * Generate a Sequential Number for each Row.
 *
 * In order to use the Index plugin it might be recommend to attach the attribute to a root attribute, as no value needs
 * to be bound to the attribute:
 *
 * ```php
 * public function ngRestExtraAttributeTypes()
 * {
 *     return [
 *         'id.index' => ['index'],
 *     ];
 * }
 * ```
 *
 * NgRestScope list config:
 *
 * ```php
 * [['list'], ['id.index']],
 * ```
 *
 * > In order to hide the primary key column take a look at [[ngrest-model.md]] hidenInList
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 3.0.0
 */
class Index extends Angular
{
    /**
     * @var string If {{config.groupBy}} is enabled, use the internal index, otherwise multiple with current page.
     */
    public $template = '
        <span ng-show="config.groupBy">
            {{ $index + 1}}
        </span>
        <span ng-show="!config.groupBy">
        {{1+((pager.currentPage-1) * pager.perPage)+$index}}
        </div>
    ';
}
