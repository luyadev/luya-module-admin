<?php

namespace luya\admin\tests\data\properties;

use luya\admin\base\Property;

class MyTestProperty extends Property
{
    public function type()
    {
        return self::TYPE_TEXT;
    }

    public function varName()
    {
        return 'barfoo';
    }

    public function label()
    {
        return 'Label';
    }
}
