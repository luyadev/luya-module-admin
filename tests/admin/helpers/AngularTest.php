<?php

namespace admintests\admin\helpers;

use admintests\AdminTestCase;
use luya\admin\helpers\Angular;

class AngularTest extends AdminTestCase
{
    public function testDirective()
    {
        $this->assertSame('<foo-bar attr="value"></foo-bar>', Angular::directive('foo-bar', ['attr' => 'value']));
        $this->assertSame('<foo-bar attr="value"></foo-bar>', Angular::directive('FooBar', ['attr' => 'value']));
    }

    public function testTextDirective()
    {
        $this->assertSame('<zaa-text model="the-model" label="the-label" fieldid="the-model-zaa-text" fieldname="the-model"></zaa-text>', Angular::text('the-model', 'the-label')->render());
        $this->assertSame('<zaa-text classAttr="value" model="the-model" label="the-label" fieldid="the-model-zaa-text" fieldname="the-model"></zaa-text>', Angular::text('the-model', 'the-label', ['classAttr' => 'value'])->render());
    }

    public function testSortRelationArrayDirective()
    {
        $this->assertSame('<zaa-sort-relation-array model="the-model" label="the-label" options=\'{"sourceData":[{"label":"bar","value":"foo"}]}\' fieldid="the-model-zaa-sort-relation-array" fieldname="the-model"></zaa-sort-relation-array>', Angular::sortRelationArray('the-model', 'the-label', ['foo' => 'bar'])->render());
        $this->assertSame('<zaa-sort-relation-array classAtr="value" model="the-model" label="the-label" options=\'{"sourceData":[{"label":"bar","value":"foo"}]}\' fieldid="the-model-zaa-sort-relation-array" fieldname="the-model"></zaa-sort-relation-array>', Angular::sortRelationArray('the-model', 'the-label', ['foo' => 'bar'], ['classAtr' => 'value'])->render());
    }

    public function testCheckbox()
    {
        $this->assertSame('<zaa-checkbox model="model" label="label" fieldid="model-zaa-checkbox" fieldname="model"></zaa-checkbox>', Angular::checkbox('model', 'label')->render());
    }

    public function testCheckboxList()
    {
        $this->assertSame('<zaa-checkbox-array model="model" label="label" options=\'{"items":[{"label":1,"value":0}]}\' fieldid="model-zaa-checkbox-array" fieldname="model"></zaa-checkbox-array>', Angular::checkboxArray('model', 'label', [0 => 1])->render());
    }

    public function testSchedule()
    {
        $this->assertSame('<luya-schedule value="model" title="label" model-class="path/to/model" attribute-name="attribute_name" attribute-values=\'[{"label":"Off","value":0},{"label":"On","value":1}]\' primary-key-value="pkvalue"></luya-schedule>', Angular::schedule('model', 'label', 'pkvalue', [0 => 'Off', 1 => 'On'], 'path/to/model', 'attribute_name')->render());
        $this->assertSame('<luya-schedule value="model" title="xyz" model-class="path/to/model" attribute-name="attribute_name" attribute-values=\'[{"label":"Off","value":0},{"label":"On","value":1}]\' primary-key-value="pkvalue"></luya-schedule>', Angular::schedule('model', 'label', 'pkvalue', [0 => 'Off', 1 => 'On'], 'path/to/model', 'attribute_name', ['title' => 'xyz'])->render());
        $this->assertSame('<luya-schedule value="model" title="label" model-class="path/to/model" attribute-name="attribute_name" attribute-values=\'[{"label":"Off","value":0},{"label":"On","value":1}]\' primary-key-value="pkvalue" only-icon="1"></luya-schedule>', Angular::schedule('model', 'label', 'pkvalue', [0 => 'Off', 1 => 'On'], 'path/to/model', 'attribute_name', ['only-icon' => 1])->render());
    }

    public function testTagArray()
    {
        $this->assertSame('<zaa-tag-array model="barfoo" label="label" fieldid="barfoo-zaa-tag-array" fieldname="barfoo"></zaa-tag-array>', Angular::tagArray('barfoo', 'label')->render());
    }
}
