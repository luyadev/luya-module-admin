<?php

namespace luya\admin\openapi\phpdoc;

class PhpDocParam
{
    protected $phpDocParser;

    protected $definition;

    public function __construct(PhpDocParser $phpDocParser, array $definition)
    {
        $this->phpDocParser = $phpDocParser;
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
        return isset($this->definition[1]) ? new PhpDocType($this->phpDocParser, $this->definition[1]) : null;
    }
}