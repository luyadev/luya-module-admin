<?php

namespace admintests\admin\ngrest\plugins;

use admintests\AdminTestCase;
use admintests\data\fixtures\UserFixture;
use admintests\data\fixtures\UserOnlineFixture;
use luya\admin\ngrest\plugins\SelectRelationActiveQuery;
use yii\base\Event;

class SelectRelationActiveQueryTest extends AdminTestCase
{
    public function testNonI18nSingleLabelOnListFind()
    {
        $event = new Event();
        $userFixture = new UserFixture();
        $userFixture->load();
        $onlineFixture = new UserOnlineFixture();
        $onlineFixture->load();

        $online = $onlineFixture->getModel('userOnline1');
        $event->sender = $online;
        $plugin = new SelectRelationActiveQuery([
            'name' => 'user_id',
            'alias' => 'user_id',
            'query' => $online->getUser(),
            'labelField' => 'email',
        ]);

        $plugin->onListFind($event);

        $this->assertSame("john@luya.io", $online->user_id);
    }

    public function testNonI18nTwoLabelsArrayOnListFind()
    {
        $event = new Event();
        $userFixture = new UserFixture();
        $userFixture->load();
        $onlineFixture = new UserOnlineFixture();
        $onlineFixture->load();

        $online = $onlineFixture->getModel('userOnline1');
        $event->sender = $online;
        $plugin = new SelectRelationActiveQuery([
            'name' => 'user_id',
            'alias' => 'user_id',
            'query' => $online->getUser(),
            'labelField' => ['firstname', 'lastname'],
        ]);

        $plugin->onListFind($event);

        $this->assertSame("John Doe", $online->user_id);
    }

    public function testNonI18nTwoLabelsStringOnListFind()
    {
        $event = new Event();
        $userFixture = new UserFixture();
        $userFixture->load();
        $onlineFixture = new UserOnlineFixture();
        $onlineFixture->load();

        $online = $onlineFixture->getModel('userOnline1');
        $event->sender = $online;
        $plugin = new SelectRelationActiveQuery([
            'name' => 'user_id',
            'alias' => 'user_id',
            'query' => $online->getUser(),
            'labelField' => 'firstname,lastname',
        ]);

        $plugin->onListFind($event);

        $this->assertSame("John Doe", $online->user_id);
    }
}
