<?php

namespace luya\admin\openapi\specs;

use cebe\openapi\spec\Schema;
use luya\admin\models\ApiUser;
use luya\admin\openapi\phpdoc\PhpDocParser;
use ReflectionClass;
use yii\db\BaseActiveRecord;

/**
 * Generate Schema Specs from Active Record.
 *
 * @author Basil Suter <git@nadar.io>
 * @since 3.2.0
 */
class ActiveRecordToSchema
{
    /**
     * @var BaseActiveRecord
     */
    protected $activeRecord;

    /**
     * @var PhpDocParser
     */
    protected $phpDocParser;

    /**
     * @var BaseSpecs
     */
    protected $baseSpecs;

    public function __construct(BaseSpecs $baseSpecs, BaseActiveRecord $activeRecord)
    {
        $this->activeRecord = $activeRecord;
        $this->baseSpecs = $baseSpecs;
        $this->phpDocParser = new PhpDocParser(new ReflectionClass(get_class($activeRecord)));
    }

    /**
     * Get Properties
     *
     * @param boolean $virtualProperties Whether virtual properties from phpdoc block `@property` should be added or not
     * @return array
     */
    public function getProperties($phpDocProperties = true)
    {
        $properties = [];
        foreach ($this->activeRecord->attributes() as $attributeName) {
            $properties[$attributeName] = $this->createSchema($attributeName);
        }

        if ($phpDocProperties) {
            foreach ($this->phpDocParser->getProperties() as $prop) {
                if (!array_key_exists($prop->getNormalizedName(), $properties)) {
                    $properties[$prop->getNormalizedName()] = $this->createSchema($prop->getNormalizedName());
                }
            }
        }
        
        return $properties;
    }
    
    public function createSchema($attributeName)
    {
        $property = $this->phpDocParser->getProperty($attributeName);
        

        $type = $property->getType();
        // handle php object type
        if ($type->getIsClass() && !$this->isCircualrReference($type->getClassName())) {
            
            $object = $this->baseSpecs->createActiveRecordSchema($type->getClassName());
            
            if ($object) {
                $config = $this->baseSpecs->activeRecordToSchema($object, $type->getIsArray());
                $config['title'] = $property->getDescription() ? $property->getDescription() : $type->getClassPhpDocParser()->getShortSummary();
                $config['description'] = $type->getClassPhpDocParser()->getLongDescription(); // @TODO veryify if <br> or PHP_EOL (\n) works, redoc seems to work with <br/>
                return new Schema($config);
            }
        }

        return new Schema([
            'type' => empty($type->getNoramlizeName()) ? 'string' : $type->getNoramlizeName(),
            'title' => $this->activeRecord->getAttributeLabel($attributeName),
            'description' => implode('<br>', array_filter([$this->activeRecord->getAttributeHint($attributeName), $property->getDescription()])), // @TODO veryify if <br> or PHP_EOL (\n) works, redoc seems to work with <br/>
        ]);
    }

    protected function isCircualrReference($class)
    {
        return trim(get_class($this->activeRecord), '\\') == trim($class, '\\');
    }

    public function guetQueryParams()
    {
        return [
            'field' => [],
            'expand' => [],
        ];
    }
}
