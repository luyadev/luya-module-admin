<?php

namespace luya\admin\openapi\phpdoc;

/**
 * Uses PhpDoc Annotation Parser
 * 
 * @since 3.3.0
 * @author Basil Suter <git@nadar.io>
 */
class PhpDocUses
{
    protected $phpDocParser;

    protected $definition;

    public function __construct(PhpDocParser $phpDocParser, array $definition)
    {
        $this->phpDocParser = $phpDocParser;
        $this->definition = $definition;
    }

    /**
     * Description.
     *
     * @return string
     */
    public function getDescription()
    {
        return isset($this->definition[2]) ? ltrim($this->definition[2], '$') : '';
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