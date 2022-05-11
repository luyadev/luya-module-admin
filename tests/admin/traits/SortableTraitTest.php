<?php

namespace admintests\admin\traits;

use admintests\AdminModelTestCase;
use admintests\AdminTestCase;
use admintests\data\fixtures\UserFixture;
use luya\admin\models\Group;
use luya\admin\models\User;
use luya\admin\models\UserOnline;
use luya\admin\traits\SortableTrait;
use luya\testsuite\fixtures\NgRestModelFixture;

class SortableTraitTest extends AdminModelTestCase
{
    public function testSorting()
    {
        new NgRestModelFixture([
            'modelClass' => UserStub::class,
            'fixtureData' => [
                'user1' => [
                    'id' => 1,
                    'title' => 1,
                    'firstname' => 'John',
                    'lastname' => 'Doe',
                    'email' => 'john@luya.io',
                    'password' => 'nohash',
                    'is_deleted' => 0,
                    'is_api_user' => 0,
                ],
                'user2' => [
                    'id' => 2,
                    'title' => 3,
                    'firstname' => 'Jane',
                    'lastname' => 'Doe',
                    'email' => 'jane@luya.io',
                    'password' => 'nohash',
                    'is_deleted' => 0,
                    'is_api_user' => 0,
                ],
                'user3' => [
                    'id' => 3,
                    'title' => 2,
                    'firstname' => 'James',
                    'lastname' => 'Doe',
                    'email' => 'deleted@luya.io',
                    'password' => 'nohash',
                    'is_deleted' => 0,
                    'is_api_user' => 0,
                ]
            ]
        ]);

        $this->createAdminNgRestLogFixture();


        // ensures the sort index title = 3 is the last item, which is array index 2
        $q = UserStub::find()->asArray()->all();
        $this->assertSame('Jane', $q[2]['firstname']);

        // get last model (jane) and move to new position
        $modelLast = UserStub::findOne(['id' => 2]);
        $modelLast->title = 1;
        $modelLast->save(true, ['title']);

        // ensures the sort index title = 3 is the last item, which is now since jane has swap to first position James
        $q = UserStub::find()->asArray()->all();
        $this->assertSame('James', $q[2]['firstname']);
    }
}

class UserStub extends User {
    
    use SortableTrait;

    public static function sortableField()
    {
        return 'title';
    }
}
