<?php

namespace luya\admin\ngrest\base;


/**
 * Active Selection Actions
 * 
 * An active selection (action) is button which can interact with the selected items from the CRUD list.
 * 
 * @author Basil Suter <git@nadar.io>
 * @since 4.0.0
 */
class ActiveSelection extends BaseActiveResponse
{
    public $label;
    
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
     *    return $this->sendSuccess('We did, what you teached.')
     * }
     * ```
     */
    public $action;

    /**
     * Undocumented function
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