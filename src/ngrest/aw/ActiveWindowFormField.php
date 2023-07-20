<?php

namespace luya\admin\ngrest\aw;

use luya\admin\helpers\Angular;
use luya\admin\helpers\AngularObject;
use yii\base\BaseObject;

/**
 * ActiveWindow ActiveField Configration
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.2.2
 */
class ActiveWindowFormField extends BaseObject
{
    /**
     * @var \luya\admin\ngrest\aw\ActiveWindowFormWidget The form widget object
     */
    public $form;

    /**
     * @var string The attribute name of the field is isued as identifier to send the post data.
     */
    public $attribute;

    /**
     * @var string Pre defined value of the option
     */
    public $value;

    /**
     * @var string|boolean A label which is used when no label is provided from class creation config
     */
    public $label = false;

    /**
     * @var array An array with key and value.
     */
    protected $parts = [];

    /**
     * @var AngularObject The angular object element which is taken to generate the input.
     */
    protected $element;

    public function init()
    {
        parent::init();
        // set text input as default element
        $this->textInput();
    }

    /**
     * Define a label for this field. If false, no label will be used, if a label is provided from the configration
     * object (form) this will be overritten by this method.
     *
     * @param string $label The label of the element
     * @return \luya\admin\ngrest\aw\ActiveField
     */
    public function label($label)
    {
        if ($label === false) {
            $this->parts['{label}'] = '';
        } else {
            $this->parts['{label}'] = $label;
        }

        return $this;
    }

    /**
     * Add a default value when initializing.
     *
     * @param string|integer $value
     * @return \luya\admin\ngrest\aw\ActiveField
     * @since 1.2.2
     */
    public function initValue($value)
    {
        if (empty($value)) {
            $this->parts['{initValue}'] = '';
        } else {
            $this->parts['{initValue}'] = "{model}='{$value}'";
        }

        return $this;
    }

    /**
     *
     * @return string
     */
    protected function getNgModel()
    {
        return 'params.'.$this->attribute;
    }

    /**
     * Text input field
     *
     * @param array $options Optional data for the text input array.
     * @return \luya\admin\ngrest\aw\ActiveField
     */
    public function textInput(array $options = [])
    {
        $this->element = Angular::text('{model}', '{label}', array_merge($options, [
            'fieldid' => $this->form->getFieldId($this->attribute),
            'ng-init' => '{initValue}',
        ]));

        return $this;
    }

    /**
     * Passwword input field
     *
     * @param array $options Optional data for the text input array.
     * @return \luya\admin\ngrest\aw\ActiveField
     */
    public function passwordInput(array $options = [])
    {
        $this->element = Angular::password('{model}', '{label}', array_merge($options, [
            'fieldid' => $this->form->getFieldId($this->attribute),
            'ng-init' => '{initValue}',
        ]));

        return $this;
    }

    /**
     * Create textarea
     *
     * @param array $options Optional data for the textarea input
     * @return \luya\admin\ngrest\aw\ActiveField
     */
    public function textarea(array $options = [])
    {
        $this->element = Angular::textarea('{model}', '{label}', array_merge($options, [
            'fieldid' => $this->form->getFieldId($this->attribute),
            'ng-init' => '{initValue}',
        ]));

        return $this;
    }

    /**
     * Generate a select dropdown with data as array.
     *
     * @param array $data
     * @param array $options
     * @return \luya\admin\ngrest\aw\ActiveField
     * @since 1.2.2
     */
    public function dropDownList(array $data, array $options = [])
    {
        $this->element = Angular::select('{model}', '{label}', $data, array_merge($options, [
            'fieldid' => $this->form->getFieldId($this->attribute),
            'ng-init' => '{initValue}',
        ]));

        return $this;
    }

    /**
     * Checkbox input
     *
     * @return ActiveField
     * @since 2.0.0
     */
    public function checkbox(array $options = [])
    {
        $this->element = Angular::checkbox('{model}', '{label}', array_merge($options, [
            'fieldid' => $this->form->getFieldId($this->attribute),
            'ng-init' => '{initValue}',
        ]));

        return $this;
    }

    /**
     * Checkbox list input
     *
     * @param array $data The items
     * @return ActiveField
     * @since 2.0.0
     */
    public function checkboxList(array $data, array $options = [])
    {
        $this->element = Angular::checkboxArray('{model}', '{label}', $data, array_merge($options, [
            'fieldid' => $this->form->getFieldId($this->attribute),
            'ng-init' => '{initValue}',
        ]));

        return $this;
    }

    /**
     * radio list input
     *
     * @param array $data The items
     * @return ActiveField
     * @since 2.0.0
     */
    public function radioList(array $data, array $options = [])
    {
        $this->element = Angular::radio('{model}', '{label}', $data, array_merge($options, [
            'fieldid' => $this->form->getFieldId($this->attribute),
            'ng-init' => '{initValue}',
        ]));

        return $this;
    }

    /**
     * image upload
     *
     * @return ActiveField
     * @since 2.0.0
     */
    public function imageUpload(array $options = [])
    {
        $this->element = Angular::imageUpload('{model}', '{label}', array_merge($options, [
            'fieldid' => $this->form->getFieldId($this->attribute),
            'ng-init' => '{initValue}',
        ]));

        return $this;
    }

    /**
     * file upload
     *
     * @return ActiveField
     * @since 2.0.0
     */
    public function fileUpload(array $options = [])
    {
        $this->element = Angular::fileUpload('{model}', '{label}', array_merge($options, [
            'fieldid' => $this->form->getFieldId($this->attribute),
            'ng-init' => '{initValue}',
        ]));

        return $this;
    }

    /**
     * date picker
     *
     * @return ActiveField
     * @since 2.0.0
     */
    public function datePicker(array $options = [])
    {
        $this->element = Angular::date('{model}', '{label}', array_merge($options, [
            'fieldid' => $this->form->getFieldId($this->attribute),
            'ng-init' => '{initValue}',
        ]));

        return $this;
    }

    /**
     * date time picker
     *
     * @return ActiveField
     * @since 2.0.0
     */
    public function datetimePicker(array $options = [])
    {
        $this->element = Angular::datetime('{model}', '{label}', array_merge($options, [
            'fieldid' => $this->form->getFieldId($this->attribute),
            'ng-init' => '{initValue}',
        ]));

        return $this;
    }

    /**
     * Render the template based on input values of $parts.
     *
     * @return string The rendered field element.
     * @since 2.0.0
     */
    public function render()
    {
        if (!isset($this->parts['{label}'])) {
            $this->label($this->label);
        }

        if (!isset($this->parts['{initValue}'])) {
            $this->initValue(null);
        }

        return str_replace([
            '{label}', '{initValue}', '{model}',
        ], [
            $this->parts['{label}'],
            $this->parts['{initValue}'],
            $this->getNgModel(),
        ], $this->element) . PHP_EOL;
    }

    /**
     * The method to set a hint value for the current render AngularObject element.
     *
     * @param string $text The hint text to display.
     * @return ActiveField
     * @since 2.3.0
     */
    public function hint($text)
    {
        $this->element->hint($text);
        return $this;
    }

    /**
     * When the element is directly forced to echo its output this method is called and the template will be
     * render with the `render()` method.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->render();
    }
}
