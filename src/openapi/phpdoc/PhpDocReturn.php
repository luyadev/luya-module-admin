<?php

namespace luya\admin\openapi\phpdoc;

/**
 * Return Object from PhpDoc.
 *
 * @author Basil Suter <git@nadar.io>
 * @since 3.2.0
 */
class PhpDocReturn
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
        return isset($this->definition[2]) ? $this->definition[2] : '';
    }

    /**
     * PhpDocType
     *
     * @return PhpDocType|false
     */
    public function getType()
    {
        return isset($this->definition[1]) ? new PhpDocType($this->phpDocParser, $this->definition[1]) : false;
    }
}
