<?php

namespace luya\admin\openapi\phpdoc;

/**
 * Php Doc Param Object.
 * 
 * The param notation is the same as for the @property annotation therfore PhpDocParm serves both param and property.
 * 
 * @author Basil Suter <git@nadar.io>
 * @since 3.2.0
 */
class PhpDocParam
{
    protected $phpDocParser;

    protected $definition;

    public function __construct(PhpDocParser $phpDocParser, array $definition)
    {
        $this->phpDocParser = $phpDocParser;
        $this->definition = $definition;
    }

    public function getDescription()
    {
        return isset($this->definition[3]) ? $this->definition[3] : '';
    }

    /**
     * PhpDocType
     *
     * @return PhpDocType
     */
    public function getType()
    {
        return new PhpDocType($this->phpDocParser, isset($this->definition[1]) ? $this->definition[1] : null);
    }
}