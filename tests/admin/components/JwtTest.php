<?php

namespace luya\admin\tests\admin\components;

use admintests\AdminTestCase;
use luya\admin\components\Jwt;

class JwtTest extends AdminTestCase
{
    public function testInvalidConfig()
    {
        $this->expectException('yii\base\InvalidConfigException');
        $component = new Jwt();
    }
}
