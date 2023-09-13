<?php

namespace luya\admin\ngrest\plugins;

use luya\admin\ngrest\base\Plugin;
use luya\Exception;

/**
 * Badge with colors for certain values.
 *
 * ```php
 * public function getLabel()
 * {
 *     return $this->status;
 * }
 *
 * public function ngRestExtraAttributeTypes()
 * {
 *     return [
 *         'label' => ['badge', 'variations' => ['Pending' => 'warning', 'Confirmed' => 'success']],
 *     ];
 * }
 * ```
 *
 * @see Available badges https://getbootstrap.com/docs/4.4/components/badge/
 * @author Basil Suter <basil@nadar.io>
 * @since 3.0.0
 */
class Badge extends Plugin
{
    /**
     * @var array An array which binds the value to a certain badge variation. Available variations:
     * - primary
     * - secondary
     * - success
     * - danger
     * - warning
     * - info
     * - light
     * - dark
     *
     * Example integration:
     *
     * ```php
     * 'variations' => [
     *     'Pending' => 'warning',
     *     'Confirmed' => 'success',
     * ]
     * ```
     */
    public $variations = [];

    public $pill = false;

    /**
     * {@inheritDoc}
     */
    public function renderList($id, $ngModel)
    {
        $pillClass = $this->pill ? 'badge-pill' : '';
        $content = '<span ng-switch="'.$ngModel.'">';
        foreach ($this->variations as $value => $variation) {
            $content .= "<span ng-switch-when=\"$value\" class=\"badge {$pillClass} badge-{$variation}\" ng-bind=\"{$ngModel}\"></span>";
        }
        $content .= "<span ng-switch-default class=\"badge {$pillClass} badge-secondary\" ng-bind=\"{$ngModel}\"></span>";
        $content .= '</span>';
        return $this->createTag('div', $content);
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception
     */
    public function renderUpdate($id, $ngModel)
    {
        throw new Exception("Badge plugin does not work in update or create context.");
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception
     */
    public function renderCreate($id, $ngModel)
    {
        return $this->renderUpdate($id, $ngModel);
    }
}
