<?php

namespace admintests\admin\proxy;

use admintests\AdminTestCase;
use luya\admin\proxy\ClientBuild;
use luya\admin\commands\ProxyController;

class ClientBuildTest extends AdminTestCase
{
    public function testIsSkippableTable()
    {
        $ctrl = new ProxyController('proxyctrl', $this->app);
        $build = new ClientBuild($ctrl, [
            'buildConfig' => ['tables' => []],
        ]);
    
        $tableFilters = [
            "cms_include_case1" => ["cms_*"],
            "cms_include_case2" => ["!admin_*"],
    
            "cms_include_case3" => ["cms_*", "admin_*"],
            "admin_include_case3" => ["cms_*", "admin_*"],
            
            "cms_include_case4" => ["cms_*", "admin_*"],
            "admin_include_case4" => ["cms_*", "admin_*"],
            
            "cms_include_case5" => ["cms_*", "admin_*", "!cms_*"],
            "admin_include_case5" => ["cms_*", "admin_*", "!cms_*"],
        ];
    
        foreach ($tableFilters as $tableName => $filters) {
            $this->assertFalse($this->invokeMethod($build, 'isSkippableTable', [$tableName, $filters]), "$tableName should be skippable by filter " . implode(', ', $filters));
        }
    }
    
    public function testIsNotSkippableTable()
    {
        $ctrl = new ProxyController('proxyctrl', $this->app);
        $build = new ClientBuild($ctrl, [
            'buildConfig' => ['tables' => []],
        ]);
    
        $tableFilters = [
            "cms_exclude_case1" => ["!cms_*"],
            "cms_exclude_case2" => ["admin_*"],
        
            "cms_exclude_case3" => ["!cms_*", "!admin_*"],
            "admin_exclude_case3" => ["!cms_*", "!admin_*"],
        
            "cms_exclude_case4" => ["!cms_*", "!admin_*"],
            "admin_exclude_case4" => ["!cms_*", "!admin_*"],
        
            "cms_exclude_case5" => ["!cms_*", "!admin_*", "cms_*"],
            "admin_exclude_case5" => ["!cms_*", "!admin_*", "cms_*"],
        ];
    
        foreach ($tableFilters as $tableName => $filters) {
            $this->assertTrue($this->invokeMethod($build, 'isSkippableTable', [$tableName, $filters]), "$tableName should not be skippable by filter " . implode(', ', $filters));
        }
    }
}
