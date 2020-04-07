<?php

namespace luya\admin\openapi\phpdoc;

use cebe\openapi\spec\Schema;
use yii\db\BaseActiveRecord;

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