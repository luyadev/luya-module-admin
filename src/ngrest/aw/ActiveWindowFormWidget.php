<?php

namespace luya\admin\ngrest\aw;

use luya\base\Widget;
use luya\Exception;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Inflector;
use yii\helpers\Json;

/**
 * ActiveWindow Callback Form Widget.
 *
 * Example usage:
 *
 * ```php
 * <?php $form = ActiveWindowFormWidget::begin(['callback' => 'get-coordinates', 'buttonValue' => 'Verify', 'angularCallbackFunction' => 'function($response) {
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
 * @since 1.2.2
 */
class ActiveWindowFormWidget extends Widget
{
    /**
     * @var array Options for the Active Form:
     *
     * - params: array, Add additional parameters which will be sent to the callback. ['foo' => 'bar']
     * - buttonClass: string, an optional class for the submit button replaces `btn`.
     * - closeOnSuccess: boolean, if enabled, the active window will close after successfully sendSuccess() response from callback.
     * - reloadListOnSuccess: boolean, if enabled, the active window will reload the ngrest crud list after success response from callback via sendSuccess().
     * - reloadWindowOnSuccess: boolean, if enabled the active window will reload itself after success (when successResponse is returned ).
     * - clearOnError: boolean, if enabled all form values will be reseted when an error happens. This is used for forms with passwords.
     */
    public $options = [];

    /**
     * @var string Required value of the Submit Button
     */
    public $buttonValue;

    /**
     * @var string Required value of the callback in the Active Window which should be triggered by this Form.
     */
    public $callback;

    /**
     * @var string Optional string with javascript callback function which is going to be triggered after angular response.
     */
    public $angularCallbackFunction = 'function() {};';

    /**
     * @var string The ActiveField class with field type methods.
     */
    public $fieldClass = '\luya\admin\ngrest\aw\ActiveWindowFormField';

    /**
     * @var array This config options are automatically used when creating a field based on the `fieldClass`.
     */
    public $fieldConfig = [];

    /**
     * @var string The name of the controller, if not defined it will generate an generic name based on date and widget id.
     * @since 2.0.0
     */
    public $controllerName;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if ($this->callback === null || $this->buttonValue === null) {
            throw new Exception("callback and/or buttonValue can not be empty");
        }

        ob_start();
    }

    /**
     * Generate a field based on attribute name and optional label.
     *
     * @param string $attribute The name of the field (which also will sent to the callback as this name)
     * @param string $label Optional Label
     * @param array $options
     * @return \luya\admin\ngrest\aw\ActiveWindowFormField
     */
    public function field($attribute, $label = null, $options = [])
    {
        $config = $this->fieldConfig;

        if (!isset($config['class'])) {
            $config['class'] = $this->fieldClass;
        }

        return Yii::createObject(ArrayHelper::merge($config, $options, [
            'attribute' => $attribute,
            'form' => $this,
            'label' => $label,
        ]));
    }

    /**
     * Convert the callback to a camlized name.
     *
     * @param string $callbackName
     * @return string
     */
    private function callbackConvert($callbackName)
    {
        return Inflector::camel2id($callbackName);
    }

    /**
     * Get the id for a field based on the attribute name
     *
     * @param string $name
     * @return string
     */
    public function getFieldId($name)
    {
        return Inflector::camel2id($this->id . $name);
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        $content = ob_get_clean();
        // do we have option params for the button
        $params = (array_key_exists('params', $this->options)) ? $this->options['params'] : [];
        // create the angular controller name
        $controller = $this->controllerName ?: 'Controller'.Inflector::camelize($this->id) . Inflector::camelize($this->callback) . time();
        // render and return the view with the specific params
        return $this->render('@admin/views/aws/base/_callbackForm', [
            'angularCrudControllerName' => $controller,
            'callbackName' => $this->callbackConvert($this->callback),
            'callbackArgumentsJson' => Json::encode($params),
            'buttonNameValue' => $this->buttonValue,
            'closeOnSuccess' => isset($this->options['closeOnSuccess']) ? '$scope.crud.closeActiveWindow();' : null,
            'reloadListOnSuccess' => isset($this->options['reloadListOnSuccess']) ? '$scope.crud.loadList();' : null,
            'reloadWindowOnSuccess' => isset($this->options['reloadWindowOnSuccess']) ? '$scope.$parent.reloadActiveWindow();' : null,
            'form' => $content,
            'angularCallbackFunction' => $this->angularCallbackFunction,
            'buttonClass' => ArrayHelper::getValue($this->options, 'buttonClass', 'btn btn-save btn-icon'),
            'clearOnError' => (int) ArrayHelper::getValue($this->options, 'clearOnError', false),
        ]);
    }
}
