<?php

namespace luya\admin\openapi\specs;

use cebe\openapi\spec\Schema;
use luya\admin\openapi\phpdoc\PhpDocParser;
use ReflectionClass;
use yii\base\Model;

/**
 * Generate Schema Specs from Active Record.
 *
 * @author Basil Suter <git@nadar.io>
 * @since 3.2.0
 */
class ActiveRecordToSchema
{
    /**
     * @var Model
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

    /**
     * @var string Contains the class which was the origin creator of the active record schema, this can be usused to determine circular references.
     */
    protected $senderActiveRecordClassName;

    public function __construct(BaseSpecs $baseSpecs, Model $activeRecord, $senderActiveRecordClassName = null)
    {
        $this->activeRecord = $activeRecord;
        $this->baseSpecs = $baseSpecs;
        $this->phpDocParser = new PhpDocParser(new ReflectionClass(get_class($activeRecord)));
        $this->senderActiveRecordClassName = $senderActiveRecordClassName;
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
        $fields = array_keys($this->activeRecord->fields());

        if (empty($fields)) {
            $fields = $this->activeRecord->attributes();
        }
        foreach ($fields as $attributeName) {
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
        if ($type->getIsClass() && !$this->isCircularReference($type->getClassName())) {
            
            $object = $this->baseSpecs->createActiveRecordSchemaObjectFromClassName($type->getClassName(), get_class($this->activeRecord));
            
            if ($object) {
                $config = $this->baseSpecs->createSchemaFromActiveRecordToSchemaObject($object, $type->getIsArray());
                $config['title'] = $property->getDescription() ? $property->getDescription() : $type->getClassPhpDocParser()->getShortSummary();
                $config['description'] = $type->getClassPhpDocParser()->getLongDescription(); // @TODO veryify if <br> or PHP_EOL (\n) works, redoc seems to work with <br/>
                return new Schema($config);
            }
        }

        if ($type->getIsScalar()) {
            return new Schema([
                'type' => $type->getNoramlizeName(),
                'title' => $this->activeRecord->getAttributeLabel($attributeName),
                'description' => implode('<br>', array_filter([$this->activeRecord->getAttributeHint($attributeName), $property->getDescription()])), // @TODO veryify if <br> or PHP_EOL (\n) works, redoc seems to work with <br/>
            ]);
        }

        return new Schema([
            'type' => $type->getNoramlizeName(),
            'items' => [
                'type' => 'string'
            ],
            'title' => $this->activeRecord->getAttributeLabel($attributeName),
            'description' => implode('<br>', array_filter([$this->activeRecord->getAttributeHint($attributeName), $property->getDescription()])), // @TODO veryify if <br> or PHP_EOL (\n) works, redoc seems to work with <br/>
        ]);
    }

    protected function isCircularReference($class)
    {
        // sender class is the same as the destination class, circular reference detected.
        if ($class == $this->senderActiveRecordClassName) {
            return true;
        }

        return trim(get_class($this->activeRecord), '\\') == trim($class, '\\');
    }
}
