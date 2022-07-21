<?php

namespace luya\admin\ngrest\base;

/**
 * Active Selection Actions
 *
 * An active selection (action) is button which can interact with the selected items from the CRUD list.
 *
 * See the [[ngrest-activeselection.md]] guide.
 *
 * @author Basil Suter <git@nadar.io>
 * @since 4.0.0
 */
class ActiveSelection extends BaseActiveResponse
{
    /**
     * @var string The button label.
     */
    public $label;

    /**
     * @var string An optional icon, see [material icons](https://material.io/icons/).
     */
    public $icon;

    /**
     * @var callable A callable which contains all selected items as first argument and the object as second argument.
     *
     * ```php
     * 'action' => function(array $items, \luya\admin\ngrest\base\ActiveSelection $context) {
     *    foreach ($items as $item) {
     *      $item->doSomethingWithObject();
     *    }
     *
     *    // sends a reload event, so the ngrest list will be reloaded afterwards.
     *    $context->sendReloadEvent();
     *
     *    return $context->sendSuccess('We did, what you teached.')
     * }
     * ```
     *
     * > Each item is an object of {{luya\admin\ngrest\base\NgRestModel}} representing the model on which the selection is attached to.
     *
     * For even quicker implementations, its possible just to return a boolean value for either a success or error message.
     *
     * ```php
     * 'action' => function(array $items) {
     *   foreach ($items as $item) {
     *       // do something with item
     *   }
     *
     *   return true;
     * }
     * ```
     */
    public $action;

    /**
     * Runs the either the callable or can overriden in a concret implementation.
     *
     * @param NgRestModel[] $items
     * @return boolean
     */
    public function handle(array $items)
    {
        $state = call_user_func($this->action, $items, $this);

        // the callable has used the sendSuccess/sendError methods.
        if (is_array($state)) {
            return $state;
        }

        return $state ? $this->sendSuccess($this->label) : $this->sendError($this->label);
    }
}
