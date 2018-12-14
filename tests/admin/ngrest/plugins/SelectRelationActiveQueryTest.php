<?php

namespace admintests\admin\ngrest\plugins;

use Yii;
use admintests\AdminTestCase;
use admintests\data\fixtures\UserFixture;
use admintests\data\fixtures\UserOnlineFixture;
use admintests\data\fixtures\I18nUserFixture;
use admintests\data\models\I18nUser;
use luya\admin\models\UserOnline;
use luya\admin\components\AdminLanguage;
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

    public function testI18nSingleLabelStringOnListFind()
    {
        $userFixture = new I18nUserFixture();
        $userFixture->load();
        $onlineFixture = new UserOnlineFixture();
        $onlineFixture->load();
        
        $online = $onlineFixture->getModel('userOnline1');
        $plugin = new SelectRelationActiveQuery([
            'name' => 'user_id',
            'alias' => 'user_id',
            'query' => $online->hasOne(I18nUser::className(), ['id' => 'user_id']),
            'labelField' => 'firstname',
        ]);

        $adminLanguageMock = $this->getAdminLanguageMock();
        Yii::$app->set('adminLanguage', $adminLanguageMock);
        
        $event = new Event();
        $lang1Online = clone $online;
        $event->sender = $lang1Online;
        $plugin->onListFind($event);
        
        $event = new Event();
        $lang2Online = clone $online;
        $event->sender = $lang2Online;
        $plugin->onListFind($event);
        
        $this->assertSame("John", $lang1Online->user_id);
        $this->assertSame("Jojo", $lang2Online->user_id);
    }

    private function getAdminLanguageMock()
    {
        $mock = $this->createMock(AdminLanguage::className());
        $mock
            ->method('getLanguages')
            ->willReturn([
                [
                    'id' => 1,
                    'name' => 'Lang1',
                    'short_code' => 'lang1',
                    'is_default' => false,
                    'is_deleted' => false,
                ],
                [
                    'id' => 2,
                    'name' => 'Lang2',
                    'short_code' => 'lang2',
                    'is_default' => true,
                    'is_deleted' => false,
                ],
            ]);
                    
        $mock
            ->method('getActiveShortCode')
            ->will($this->onConsecutiveCalls('lang1', 'lang2'));
        
        return $mock;
    }

    public function testI18nMixedLabelStringOnListFind()
    {
        $userFixture = new I18nUserFixture();
        $userFixture->load();
        $onlineFixture = new UserOnlineFixture();
        $onlineFixture->load();
        
        $online = $onlineFixture->getModel('userOnline1');
        $plugin = new SelectRelationActiveQuery([
            'name' => 'user_id',
            'alias' => 'user_id',
            'query' => $online->hasOne(I18nUser::className(), ['id' => 'user_id']),
            'labelField' => 'firstname,email',
        ]);

        $adminLanguageMock = $this->getAdminLanguageMock();
        Yii::$app->set('adminLanguage', $adminLanguageMock);
        
        $event = new Event();
        $lang1Online = clone $online;
        $event->sender = $lang1Online;
        $plugin->onListFind($event);
        
        $event = new Event();
        $lang2Online = clone $online;
        $event->sender = $lang2Online;
        $plugin->onListFind($event);
        
        $this->assertSame("John john@luya.io", $lang1Online->user_id);
        $this->assertSame("Jojo john@luya.io", $lang2Online->user_id);
    }
}
