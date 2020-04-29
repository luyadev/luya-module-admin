<?php

namespace admintests\models;

use admintests\AdminModelTestCase;
use Imagine\Exception\RuntimeException;
use Imagine\Image\ImageInterface;
use luya\admin\models\StorageEffect;
use luya\admin\models\StorageFilter;
use luya\admin\models\StorageFilterChain;
use luya\testsuite\fixtures\NgRestModelFixture;
use luya\testsuite\traits\AdminDatabaseTableTrait;
use Yii;
use yii\base\InvalidConfigException;

class StorageImageTest extends AdminModelTestCase
{
    use AdminDatabaseTableTrait;

    public function testMethods()
    {

    }
}