<?php

namespace luya\admin\tests\admin\jobs;

use admintests\AdminConsoleSqLiteTestCase;
use luya\admin\filters\SmallCrop;
use luya\admin\jobs\ImageFilterJob;
use luya\testsuite\traits\AdminDatabaseTableTrait;

class ImageFilterJobTest extends AdminConsoleSqLiteTestCase
{
    use AdminDatabaseTableTrait;

    public function testExectue()
    {
        $this->createAdminStorageFileFixture();
        $this->createAdminStorageImageFixture();
        $job = new ImageFilterJob();
        $job->fileId = 1;
        $job->filterIdentifiers = [SmallCrop::identifier()];
        $this->assertEmpty($job->execute($this->app->getModule('admin')->adminqueue));
    }
}
