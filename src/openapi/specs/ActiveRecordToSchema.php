<?php

namespace luya\admin\openapi\specs;

use cebe\openapi\spec\Schema;
use yii\db\BaseActiveRecord;

/**
 * Generate Schema Specs from Active Record.
 * 
 * @author Basil Suter <git@nadar.io>
 * @since 3.2.0
 */
class ActiveRecordToSchema
{
    protected $activeRecord;

    public function __construct(BaseActiveRecord $activeRecord)
    {
        $this->activeRecord = $activeRecord;    
    }

    public function getProperties()
    {
        $properties = [];
        foreach ($this->activeRecord->attributes() as $attributeName) {
            $properties[$attributeName] = new Schema([
                'type' => 'string', // @todo read type form phpdoc or maybe rules() defintion.
                'title' => $this->activeRecord->getAttributeLabel($attributeName),
                'description' => $this->activeRecord->getAttributeHint($attributeName),
            ]);
        }
        
        return $properties;
    }
}