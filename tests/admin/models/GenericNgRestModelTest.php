<?php

namespace admintests\models;

use admintests\AdminModelTestCase;
use luya\admin\models\ApiUser;
use luya\admin\models\Config;
use luya\admin\models\Lang;
use luya\admin\models\Logger;
use luya\admin\models\NgrestLog;
use luya\admin\models\ProxyBuild;
use luya\admin\models\ProxyMachine;
use luya\admin\models\QueueLog;
use luya\admin\models\QueueLogError;
use luya\admin\models\StorageEffect;
use luya\admin\models\StorageFilter;
use luya\admin\models\StorageImage;
use luya\admin\models\Tag;
use luya\admin\models\User;
use luya\testsuite\fixtures\NgRestModelFixture;
use yii\db\ActiveQuery;

class GenericNgRestModelTest extends AdminModelTestCase
{
    public $modelClasses = [
        ApiUser::class,
        Config::class,
        Lang::class,
        Logger::class,
        NgrestLog::class,
        ProxyBuild::class,
        ProxyMachine::class,
        QueueLog::class,
        QueueLogError::class,
        StorageEffect::class,
        StorageFilter::class,
        StorageImage::class,
        Tag::class,
        User::class,
    ];

    public function testGenericMethodsForCoverageAndSyntaxErrors()
    {
        foreach ($this->modelClasses as $model) {
            $fixture = new NgRestModelFixture([
                'modelClass' => $model,
            ]);

            $item = $fixture->newModel;

            $this->assertTrue(is_array($item->ngRestScopes()));
            $this->assertInstanceOf(ActiveQuery::class, $item->ngRestFullQuerySearch('query'));
            $this->assertNotNull($item->attributeHints());
            $this->assertNotNull($item->attributeLabels());
            $this->assertNotNull($item->rules());
            $this->assertTrue(is_array($item->extraFields()));
            $this->assertTrue(is_array($item->fields()));
        }
    }
}
