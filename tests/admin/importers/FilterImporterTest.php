<?php

namespace admintests\admin\importers;

use admintests\AdminConsoleSqLiteTestCase;
use luya\admin\importers\FilterImporter;
use luya\admin\models\StorageEffect;
use luya\admin\models\StorageFile;
use luya\admin\models\StorageFilter;
use luya\admin\models\StorageFilterChain;
use luya\admin\models\StorageImage;
use luya\console\commands\ImportController;
use luya\testsuite\fixtures\NgRestModelFixture;

class FilterImporterTest extends AdminConsoleSqLiteTestCase
{
    public function testInvalidObject()
    {
        new NgRestModelFixture(['modelClass' => StorageEffect::class]);
        new NgRestModelFixture(['modelClass' => StorageFilter::class]);
        new NgRestModelFixture(['modelClass' => StorageFilterChain::class]);
        new NgRestModelFixture(['modelClass' => StorageFile::class]);
        new NgRestModelFixture(['modelClass' => StorageImage::class]);

        $base = new ImportController('importer', $this->app);
        $importer = new FilterImporter($base, $this->app->getModule('admin'));
        $importer->run();
        $this->assertNotEmpty($base->getLog());
    }
}
