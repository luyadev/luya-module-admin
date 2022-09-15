<?php

namespace admintests\admin\traits;

use admintests\AdminModelTestCase;
use luya\admin\traits\LazyDataLoadTrait;

class LazyDataLoadTraitTest extends AdminModelTestCase
{
    public function testWithAnonymousFunction()
    {
        $dataLoaded = false;

        $mock = new LazyDataLoadTraitMock();
        $mock->data = function () use (&$dataLoaded) {
            $dataLoaded = true;
            return 'FOO';
        };

        $this->assertEquals('FOO', $mock->getData());
        $this->assertTrue($dataLoaded);
    }

    public function testWithCallableArray()
    {
        $this->lazyDataLoadFunctionCalled = false;

        $mock = new LazyDataLoadTraitMock();
        $mock->data = [$this, 'lazyDataLoadFunction'];

        $this->assertEquals(self::class . '::lazyDataLoadFunction', $mock->getData());
        $this->assertTrue($this->lazyDataLoadFunctionCalled);
    }

    protected $lazyDataLoadFunctionCalled = false;

    public function lazyDataLoadFunction()
    {
        $this->lazyDataLoadFunctionCalled = true;

        return __METHOD__;
    }
}

class LazyDataLoadTraitMock
{
    use LazyDataLoadTrait;

    public $data;

    public function getData()
    {
        return $this->lazyLoadData($this->data);
    }
}
