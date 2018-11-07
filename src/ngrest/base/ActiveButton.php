<?php

namespace luya\admin\ngrest\base;

use yii\base\BaseObject;

/**
 * Active Button Base Class.
 * 
 * An active button is a trigger option for the current model.
 * 
 * @author Basil Suter <basil@nadar.io>
 * @since 1.2.3
 */
abstract class ActiveButton extends BaseObject
{
    const EVENT_RELOAD_LIST = 'loadList';
    /**
     * A label value. You can also access different angular list  fields when using brackets:
     * 
     * 'label' => '{fieldname}',
     *
     * @var [type]
     */
    public $label;

    public $icon = 'extension';

    private $_events = [];

    abstract public function handle(NgRestModel $model);
    
    protected function sendReloadEvent()
    {
        $this->_events[] = self::EVENT_RELOAD_LIST;
    }

    public function sendError($message)
    {
        return [
            'success' => false,
            'message' => $message,
            'events' => $this->_events,
        ];
    }

    public function sendSuccess($message)
    {
        return [
            'success' => true,
            'message' => $message,
            'events' => $this->_events,
        ];
    }
}