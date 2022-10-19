<?php

namespace luya\admin\ngrest\base;

use luya\admin\components\Auth;
use luya\admin\ngrest\NgRestButtonConditionInterface;
use luya\admin\ngrest\NgRestPermissionLevelInterface;

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
 *     public function getDefaultLabel()
 *     {
 *          return 'Campaign';
 *     }
 *
 *     public function getDefaultIcon()
 *     {
 *          return 'extension';
 *     }
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
 * Integration of the Button:
 *
 * ```php
 * public function ngRestActiveButtons()
 * {
 *     return [
 *          ['class' => CreateCampaignActiveButton::class],
 *     ];
 * }
 * ```
 *
 * The label an icon can override the default label and default icon:
 *
 * ```php
 * ['class' => CreateCampaignActiveButton::class, 'label' => 'My Label', 'icon' => 'myicon'],
 * ```
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.2.3
 */
abstract class ActiveButton extends BaseActiveResponse implements NgRestButtonConditionInterface, NgRestPermissionLevelInterface
{
    /**
     * Get the default label if not set trough {{$label}}.
     *
     * @return string|boolean
     */
    public function getDefaultLabel()
    {
        return false;
    }

    private $_label;

    /**
     * Set label
     *
     * @param string $label The label for the button.
     */
    public function setLabel($label)
    {
        $this->_label = $label;
    }

    /**
     * Get the button label, if not set default label is used.
     *
     * If label is false or null, the button has no label.
     *
     * @return string|boolean
     */
    public function getLabel()
    {
        return $this->_label ?: $this->getDefaultLabel();
    }

    /**
     * The default icon of not overriden trough {{$icon}}.
     *
     * @return string
     */
    public function getDefaultIcon()
    {
        return 'extension';
    }

    private $_icon;

    /**
     * Setter method for icon
     *
     * @param string $icon The material icon
     */
    public function setIcon($icon)
    {
        $this->_icon = $icon;
    }

    /**
     * Get the button icon, if icon is not set default icon is used.
     *
     * @return string
     */
    public function getIcon()
    {
        return $this->_icon ?: $this->getDefaultIcon();
    }

    private $_condition;

    /**
     * @inheritdoc
     */
    public function setCondition($condition)
    {
        $this->_condition = $condition;
    }

    /**
     * @inheritdoc
     */
    public function getCondition()
    {
        return empty($this->_condition) ? '' : $this->_condition;
    }

    private int $_permissionLevel = Auth::CAN_UPDATE;

    /**
     * @inheritdoc
     */
    public function setPermissionLevel($permissionLevel)
    {
        $this->_permissionLevel = $permissionLevel;
    }

    /**
     * @inheritdoc
     */
    public function getPermissionLevel()
    {
        return $this->_permissionLevel;
    }

    /**
     * The handler which implements the function of the button.
     *
     * The model is passed as arugment and is refereing to the current model the active button has been pushed.
     *
     * @param NgRestModel $model
     * @return array See sendError() or sendSuccess().
     */
    abstract public function handle(NgRestModel $model);
}
