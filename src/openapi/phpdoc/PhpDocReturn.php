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
    public function __construct(protected PhpDocParser $phpDocParser, protected array $definition)
    {
    }

    public function getDescription()
    {
        return $this->definition[2] ?? '';
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
