<?php

namespace luya\admin\tests\admin\beheaviors;

use admintests\AdminModelTestCase;
use luya\admin\behaviors\BlameableBehavior;
use yii\base\Event;

class BlameableBehaviorTest extends AdminModelTestCase
{
    public function testGetValue()
    {
        $behavior = new BlameableBehavior();

        $this->assertNull($this->invokeMethod($behavior, 'getValue', [new Event()]));
    }
}
