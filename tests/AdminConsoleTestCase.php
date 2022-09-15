<?php

namespace admintests;

use luya\base\Boot;
use luya\testsuite\cases\BaseTestSuite;

require 'vendor/autoload.php';
require 'data/env.php';

class AdminConsoleTestCase extends BaseTestSuite
{
    public function getConfigArray()
    {
        return include(__DIR__ .'/data/configs/console.php');
    }

    public function bootApplication(Boot $boot)
    {
        $boot->applicationConsole();
    }

    protected function removeNewline($text)
    {
        $text = trim(preg_replace('/\s+/', ' ', $text));

        return str_replace(['> ', ' <'], ['>', '<'], $text);
    }
}
