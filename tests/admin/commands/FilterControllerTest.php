<?php

namespace admintests\admin\commands;

use admintests\AdminConsoleTestCase;
use luya\admin\commands\FilterController;
use luya\console\Application;

class FilterControllerTest extends AdminConsoleTestCase
{
    public function testIndexAction()
    {
        $app = new Application($this->getConfigArray());
        $ctrl = new FilterController('index', $app);

        $this->assertTrue(is_object($ctrl));

        $buff = <<<'EOT'
<?php

namespace app\filters;

use luya\admin\base\Filter;

/**
 * Nam Filter.
 *
 * File has been created with `block/create` command. 
 */
class className extends Filter
{
    public static function identifier()
    {
        return 'idf';
    }

    public function name()
    {
        return 'Nam';
    }

    public function chain()
    {
        return [
            [method, [
                'arg' => 100,
                'foo' => null,
            ]],
        ];
    }
}
EOT;

        $render = $this->removeNewline($ctrl->generateClassView('idf', 'Nam', ['method' => ['arg' => 100, 'foo' => null]], 'className'));
        $this->assertSame($this->removeNewline($buff), $render);
    }
}
