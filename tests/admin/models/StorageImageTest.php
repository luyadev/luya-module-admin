<?php

namespace admintests\models;

use admintests\AdminModelTestCase;
use luya\admin\models\StorageFilter;
use luya\admin\models\StorageImage;
use luya\testsuite\fixtures\NgRestModelFixture;
use luya\testsuite\traits\AdminDatabaseTableTrait;
use yii\db\ActiveQuery;

class StorageImageTest extends AdminModelTestCase
{
    use AdminDatabaseTableTrait;

    public function testMethods()
    {
        $fixture = new NgRestModelFixture([
            'modelClass' => StorageImage::class,
        ]);

        new NgRestModelFixture(['modelClass' => StorageFilter::class]);

        $model = $fixture->newModel;
        $this->assertInstanceOf(ActiveQuery::class, $model->getImages());
        $this->assertInstanceOf(ActiveQuery::class, $model->getFile());
        $this->assertInstanceOf(ActiveQuery::class, $model->getFilter());
        $this->assertInstanceOf(ActiveQuery::class, $model->getTinyCropImage());
        $this->assertInstanceOf(ActiveQuery::class, $model->getMediumThumbnailImage());
    }
}
