<?php

namespace luya\admin\openapi\phpdoc;

/**
 * Php Doc Param Object.
 *
 * The param notation is the same as for the @property annotation therefore PhpDocParm serves both param and property.
 *
 * @author Basil Suter <git@nadar.io>
 * @since 3.2.0
 */
class PhpDocParam
{
    public function __construct(protected PhpDocParser $phpDocParser, protected array $definition)
    {
    }

    /**
     * Get name ($name) of the variable.
     *
     * @return string
     */
    public function getName()
    {
        return isset($this->definition[2]) ? trim($this->definition[2]) : '';
    }

    /**
     * Get attribute name without dollaer.
     *
     * @return void
     */
    public function getNormalizedName()
    {
        return ltrim($this->getName(), '$');
    }

    /**
     * Description.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->definition[3] ?? '';
    }

    /**
     * PhpDocType
     *
     * @return PhpDocType
     */
    public function getType()
    {
        return new PhpDocType($this->phpDocParser, $this->definition[1] ?? null);
    }
}
