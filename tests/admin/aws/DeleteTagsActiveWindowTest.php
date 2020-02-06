<?php

namespace tests\admin\aws;

use admintests\AdminModelTestCase;
use luya\admin\aws\DeleteTagsActiveWindow;
use luya\admin\models\NgrestLog;
use luya\admin\models\Tag;
use luya\admin\models\TagRelation;
use luya\testsuite\fixtures\NgRestModelFixture;
use luya\testsuite\traits\AdminDatabaseTableTrait;

class DeleteTagsActiveWindowTest extends AdminModelTestCase
{
    use AdminDatabaseTableTrait;

    protected $langFixture;
    protected $tagRelationFixture;
    protected $tagFixture;
    protected $logFixture;

    public function makeFixtures()
    {
        $this->logFixture = new NgRestModelFixture(['modelClass' => NgrestLog::class]);
        $this->langFixture = $this->createAdminLangFixture([]);

        $this->tagFixture = new NgRestModelFixture([
            'modelClass' => Tag::class,
            'fixtureData' => [
                1 => [
                    'id' => 1,
                    'name' => 'foobar',
                ]
            ]
        ]);

        $this->tagRelationFixture = new NgRestModelFixture([
            'modelClass' => TagRelation::class,
            'fixtureData' => [
                1 => [
                    'pk_id' => 1,
                    'table_name' => 'test1',
                    'tag_id' => 1,
                ],
                2 => [
                    'pk_id' => 2,
                    'table_name' => 'test1',
                    'tag_id' => 1,
                ],
                3 => [
                    'pk_id' => 1,
                    'table_name' => 'test2',
                    'tag_id' => 1,
                ],
            ]
        ]);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testRender()
    {
        $this->makeFixtures();
        $tagModel = $this->tagFixture->getData(1);

        $aws = new DeleteTagsActiveWindow();
        $aws->ngRestModelClass = Tag::class;
        $aws->itemId = 1;

        $html = $aws->index();
        $this->assertContains('test1', $html);
        $this->assertContains('test2', $html);

        $this->assertSame('foobar', $aws->getTitle());

        // run remove callback

        $response = $aws->callbackRemove('foobar');

        $this->assertSame([
            'error' => false,
            'message' => 'aws_delete_remove_success',
            'responseData' => [],
        ], $response);

        $this->cleanupFixtures();
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testErrorCallback()
    {
        $this->makeFixtures();
        $tagModel = $this->tagFixture->getData(1);

        $aws = new DeleteTagsActiveWindow();
        $aws->ngRestModelClass = Tag::class;
        $aws->itemId = 1;

        $response = $aws->callbackRemove('unknown');

        $this->assertSame([
            'error' => true,
            'message' => 'aws_delete_remove_wrong_name',
            'responseData' => [],
        ], $response);

        $this->cleanupFixtures();
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testErrorExceptionCallback()
    {
        $aws = new DeleteTagsActiveWindow();
        $this->assertSame('delete', $aws->defaultIcon());
    }

    public function cleanupFixtures()
    {
        if ($this->tagFixture) {
            $this->tagFixture->cleanup();
        }
        if ($this->tagRelationFixture) {
            $this->tagRelationFixture->cleanup();
        }
        if ($this->langFixture) {
            $this->langFixture->cleanup();
        }

        if ($this->logFixture) {
            $this->logFixture->cleanup();
        }
    }
}
