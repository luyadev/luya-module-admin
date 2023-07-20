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
     * @var PhpDocParser
     */
    protected $phpDocParser;

    /**
     * @var string Contains the class which was the origin creator of the active record schema, this can be usused to determine circular references.
     */
    protected $senderActiveRecordClassName = [];

    public function __construct(protected BaseSpecs $baseSpecs, protected Model $activeRecord, $senderActiveRecordClassName = null)
    {
        $this->phpDocParser = new PhpDocParser(new ReflectionClass($activeRecord::class));
        $this->senderActiveRecordClassName = (array) $senderActiveRecordClassName;
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
        $attributeFields = [];

        $fields = $this->activeRecord->fields();
        if (!empty($fields)) {
            foreach ($fields as $key => $value) {
                if (is_numeric($key)) {
                    $attributeFields[] = $value;
                } else {
                    $attributeFields[] = $key;
                }
            }
        } else {
            $attributeFields = $this->activeRecord->attributes();
        }

        foreach ($attributeFields as $attributeName) {
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
            $object = $this->baseSpecs->createActiveRecordSchemaObjectFromClassName($type->getClassName(), array_merge([$this->activeRecord::class], $this->senderActiveRecordClassName));

            if ($object) {
                $config = $this->baseSpecs->createSchemaFromActiveRecordToSchemaObject($object, $type->getIsArray());
                $config['title'] = $property->getDescription() ?: $type->getClassPhpDocParser()->getShortSummary();
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
        if (in_array($class, $this->senderActiveRecordClassName)) {
            return true;
        }

        return trim($this->activeRecord::class, '\\') == trim($class, '\\');
    }
}
