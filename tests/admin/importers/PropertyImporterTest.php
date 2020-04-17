<?php

namespace admintests\admin\importers;

use admintests\AdminConsoleSqLiteTestCase;
use luya\admin\importers\PropertyImporter;
use luya\admin\models\Property;
use luya\console\commands\ImportController;
use luya\testsuite\fixtures\NgRestModelFixture;

class PropertyImporterTest extends AdminConsoleSqLiteTestCase
{
    public function testInvalidObject()
    {
        new NgRestModelFixture(['modelClass' => Property::class]);

        // data properties folder
        $path = dirname(__DIR__) . '/../data/properties';
        $folder = 'properties';
        $ns = '\luya\admin\tests\data\properties';
        $module = 'admin';



        $base = new ImportController('importer', $this->app);
        $this->invokeMethod($base, 'addToDirectory', [
            $path, $folder, $ns, $module
            // $path, $folderName, $ns, $module
        ]);

        $importer = new PropertyImporter($base, $this->app->getModule('admin'));
        $importer->run();


        $this->assertNotEmpty($base->getLog());
    }
}
