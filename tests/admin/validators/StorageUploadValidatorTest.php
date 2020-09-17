<?php

namespace luya\admin\tests\admin\validators;

use admintests\AdminModelTestCase;
use luya\admin\validators\StorageUploadValidator;
use luya\base\DynamicModel;

class StorageUploadValidatorTest extends AdminModelTestCase
{
    public function testValidateAttribute()
    {
        $validator = new StorageUploadValidator();

        $model = new DynamicModel(['file_id' => 0]);

        $response = $validator->validateAttribute($model, 'file_id');
        
        $this->assertNull($response);
    }
}