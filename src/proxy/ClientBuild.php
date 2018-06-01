<?php

namespace luya\admin\proxy;

use Yii;
use luya\console\Command;
use yii\base\InvalidConfigException;
use luya\helpers\StringHelper;
use yii\base\BaseObject;

/**
 * Admin Proxy Build.
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.0
 */
class ClientBuild extends BaseObject
{
    /**
     * @var \luya\console\Command $command object
     */
    public $command;
    
    public $buildToken;
    
    public $requestUrl;
    
    public $requestCloseUrl;
    
    public $fileProviderUrl;
    
    public $imageProviderUrl;
    
    public $machineIdentifier;

    public $machineToken;
    
    public $storageFilesCount;
    
    public $optionStrict;

    public $syncRequestsCount;
    
    private $_optionTable;
    
    public function setOptionTable($table)
    {
        if (!empty($table)) {
            $this->_optionTable = explode(",", $table);
        }
    }
    
    public function getOptionTable()
    {
        return $this->_optionTable;
    }
    
    public function __construct(Command $command, array $config = [])
    {
        $this->command = $command;
        parent::__construct($config);
    }
    
    public function init()
    {
        parent::init();
        
        if ($this->_buildConfig === null) {
            throw new InvalidConfigException("build config can not be empty!");
        }
    }
    
    private $_buildConfig;

    public function setBuildConfig(array $config)
    {
        $this->_buildConfig = $config;

        foreach ($config['tables'] as $tableName => $tableConfig) {
            if (!empty($this->optionTable)) {
                if ($this->isSkippableTable($tableName, $this->optionTable)) {
                    continue;
                }
            }

            $schema = Yii::$app->db->getTableSchema($tableName);

            if ($schema !== null) {
                $this->_tables[$tableName] = new ClientTable($this, $tableConfig);
            }
        }
    }

    /**
     * Compare the tableName with the given filters.
     *
     * Example filters:
     *
     *  "cms_*" include only cms_* tables
     *  "cms_*,admin_*" include only cms_* and admin_* tables
     *  "!cms_*" exclude all cms_* tables
     *  "!cms_*,!admin_*" exclude all cms_*and admin_* tables
     *  "cms_*,!admin_*" include all cms_* tables but exclude all admin_* tables
     *
     * Only first match is relevant:
     *  "cms_*,!admin_*,admin_*" include all cms_* tables but exclude all admin_* tables (last match has no effect)
     *  "cms_*,admin_*,!admin_*" include all cms_* and admin_* tables (last match has no effect)
     *
     * @param $tableName
     * @param array $filters Array of tables which should skipped.
     * @return bool True if table can be skipped.
     * @since 1.2.1
     */
    private function isSkippableTable($tableName, array $filters)
    {
        $skip = true;

        foreach ($filters as $filter) {
            $exclude = false;
            if (substr($filter, 0, 1) == "!") {
                $exclude = true;
                $skip = false;

                $filter = substr($filter, 1);
            }
            
            if ($filter == $tableName || StringHelper::startsWithWildcard($tableName, $filter)) {
                return $exclude;
            }
        }

        return $skip;
    }

    public function getStorageFilesCount()
    {
        return $this->_buildConfig['storageFilesCount'];
    }

    private $_tables = [];

    public function getTables()
    {
        return $this->_tables;
    }
}
