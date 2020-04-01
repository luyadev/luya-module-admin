<?php

namespace luya\admin\openapi\phpdoc;

use ReflectionClass;
use yii\base\Controller;

class DocReaderController extends BaseDocReader
{
    protected $reflection;

    public function __construct(Controller $controller)
    {
        $this->reflection = new ReflectionClass(get_class($controller));
    }

    public function getReflection()
    {
        return $this->reflection;
    }
}