<?php

namespace admintests\admin\traits;

use admintests\AdminModelTestCase;
use luya\admin\models\User;
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

        // ensures the sort index title = 3 is the last item, which is array index 2
        $q = UserStub::ngRestFind()->asArray()->all();
        $this->assertSame('Jane', $q[2]['firstname']);

        // get last model (jane) and move to new position
        $modelLast = UserStub::findOne(['id' => 2]);
        $modelLast->title = 1;
        $modelLast->save(true, ['title']);

        // ensures the sort index title = 3 is the last item, which is now since jane has swap to first position James
        $q = UserStub::find()->asArray()->all();
        $this->assertSame('James', $q[2]['firstname']);

        // delete the model
        $modelLast->delete();

        // add new item
        $newModel = new UserStub();
        $newModel->title = 20;
        $newModel->firstname = 'Han';
        $newModel->lastname = 'Solo';
        $newModel->email = 'hansolo@luya.io';
        $newModel->password = 'doesnotexis434!@Aasdfts';
        $newModel->is_deleted = false;
        $this->assertTrue($newModel->save());

        $newModel->refresh();

        // the index has changed by the sortable plugin, even we have entered 20 as value
        // since we have deleted an item, the index has now 3 entries and not 4
        $this->assertSame('3', $newModel->title);
    }
}

class UserStub extends User
{
    use SortableTrait;

    public static function sortableField()
    {
        return 'title';
    }
}
