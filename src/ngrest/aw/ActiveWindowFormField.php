<?php

namespace luya\admin\ngrest\aw;

use luya\admin\helpers\Angular;
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
     * @var \luya\admin\ngrest\aw\CallbackFormWidget The form widget object
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
    
    protected $parts = [];
    
    protected $element;
    
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
        $this->element = Angular::text('{model}', '{label}', [
            'fieldid' => $this->form->getFieldId($this->attribute),
            'ng-init' => '{initValue}',
        ]);
        
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
        $this->element = Angular::password('{model}', '{label}', [
            'fieldid' => $this->form->getFieldId($this->attribute),
            'ng-init' => '{initValue}',
        ]);
        
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
        $this->element = Angular::textarea('{model}', '{label}', [
            'fieldid' => $this->form->getFieldId($this->attribute),
            'ng-init' => '{initValue}',
        ]);
        
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
        $this->element = Angular::select('{model}', '{label}', $data, [
            'fieldid' => $this->form->getFieldId($this->attribute),
            'ng-init' => '{initValue}',
        ]);
        
        return $this;
    }
    
    /**
     * Render the template based on input values of $parts.
     *
     * @return string
     */
    private function render()
    {
        if (empty($this->element)) {
            $this->textInput();
        }
        
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
        ], $this->element);
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
