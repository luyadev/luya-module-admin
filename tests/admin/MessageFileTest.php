<?php

namespace admintests;

use luya\testsuite\traits\MessageFileCompareTrait;
use Yii;

class MessageFileTest extends AdminTestCase
{
    use MessageFileCompareTrait;

    public function testFiles()
    {
        $this->compareMessages(Yii::getAlias('@admin/messages'), 'en');
    }
}
