<?php

namespace luya\admin\ngrest\base;

use Yii;
use yii\base\BaseObject;

/**
 * Active Button Base Class.
 * 
 * An active button is a trigger option for the current model.
 * 
 * Example integration:
 * 
 * ```php
 * class CreateCampaignActiveButton extends ActiveButton
 * {
 *     public $label = 'Campaign Button';
 *     public $icon = 'extension';
 * 
 *     public function handle(\luya\admin\ngrest\base\NgRestModel $model)
 *     {
 *         // do something with the $model
 *         $model->udpateAttributes(['campagin' => 123]);
 * 
 *         // maybe you change value which should be visible in the list, then you can trigger a reload event.
 *         $this->sendReloadEvent();
 * 
 *         // let the crud know everything was good and inform user with a message.
 *         return $this->sendSuccess('Campaign done for ' . $model->title);
 *     }
 * }
 * ```
 * 
 * @author Basil Suter <basil@nadar.io>
 * @since 1.2.3
 */
abstract class ActiveButton extends BaseObject
{
    /**
     * @var string The loadList event name
     */
    const EVENT_RELOAD_LIST = 'loadList';

    public function getDefaultLabel()
    {
        return false;
    }

    private $_label;

    public function setLabel($label)
    {
        $this->_label = $label;
    }

    public function getLabel()
    {
        return $this->_label ?: $this->getDefaultLabel();
    }
    
    /**
     * @var string  A label value. You can also access different angular list  fields when using brackets:
     * 
     * 'label' => '{fieldname}',
     */
    //public $label;

    public function getDefaultIcon()
    {
        return 'extension';
    }

    private $_icon;

    public function setIcon($icon)
    {
        $this->_icon = $icon;
    }

    public function getIcon()
    {
        return $this->_icon ?: $this->getDefaultIcon();
    }

    /**
     * @var string The icon from material icons list
     */
    //public $icon = 'extension';

    private $_events = [];

    /**
     * The handler which implements the function of the button.
     * 
     * The model is passed as arugment and is refereing to the current model the active button has been pushed.
     *
     * @param NgRestModel $model
     * @return array See sendError() or sendSuccess().
     */
    abstract public function handle(NgRestModel $model);
    
    /**
     * Send a crud reload event.
     * 
     * @return void
     */
    protected function sendReloadEvent()
    {
        $this->_events[] = self::EVENT_RELOAD_LIST;
    }

    /**
     * Send an error message as response. 
     * 
     * Events are only triggered on success messages {{sendSuccess()}}.
     *
     * @param string $message The error message.
     * @return array
     */
    public function sendError($message)
    {
        Yii::$app->response->setStatusCode(422, 'Data Validation Failed.');

        return [
            'success' => false,
            'message' => $message,
            'events' => $this->_events,
        ];
    }

    /**
     * Send a success message.
     *
     * @param string $message The sucess message.
     * @return array
     */
    public function sendSuccess($message)
    {
        return [
            'success' => true,
            'message' => $message,
            'events' => $this->_events,
        ];
    }
}