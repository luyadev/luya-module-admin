<?php

namespace luya\admin\ngrest\aw;

use Yii;
use luya\Exception;
use yii\helpers\Json;
use yii\helpers\Inflector;
use yii\helpers\ArrayHelper;
use luya\base\Widget;

/**
 * ActiveWindow Callback Form Widget.
 *
 * Example usage:
 *
 * ```php
 * <?php $form = CallbackFormWidget::begin(['callback' => 'get-coordinates', 'buttonValue' => 'Verify', 'angularCallbackFunction' => 'function($response) {
 *
 * console.log($response)
 *
 * };']); ?>
 *
 * <?= $form->field('firstname'); ?>
 * // equals
 * <?= $this->field('firstname')->textInput(); ?>
 *
 * // labels
 * <?= $form->field('firstname', 'Firstname Label')->textInput(); ?>
 * // equals
 * <?= $form->field('firstname')->textInput()->label('Firstname Label'); ?>
 *
 * // textarea
 * <?= $form->field('text')->textarea(); ?>
 *
 * <?php $form::end(); ?>
 * ```
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.0
 * @deprecated since 1.2.2 use ActiveWindowFormWidget instead.
 */
class CallbackFormWidget extends ActiveWindowFormWidget
{
    
}
