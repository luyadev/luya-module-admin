<?php

namespace luya\admin\ngrest\plugins;

/**
 * URL Plugin.
 *
 * This plugin allows you to make an attribute clickable, therefure it should be used on LINK input.
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 2.0.0
 */
class Url extends Text
{
    /**
     * @var string Define an attribute which is also in the list view and should be taken to render the href value of the link element.
     *
     * ```php
     * ['link', 'linkAttribute' => 'the_attribute_with_the_url'],
     * ```
     */
    public $linkAttribute;

    /**
     * @inheritDoc
     */
    public function renderList($id, $ngModel)
    {
        $linkAttribute = $this->linkAttribute ? $this->appendFieldNgModelContext($this->linkAttribute, self::LIST_CONTEXT_PREFIX) : $ngModel;
        return $this->createTag('a', "{{{$ngModel}}}", array_merge(['ng-href' => "{{{$linkAttribute}}}", 'target' => '_blank'], $this->listOptions));
    }
}
