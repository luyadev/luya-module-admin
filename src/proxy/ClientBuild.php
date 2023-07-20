<?php

namespace luya\admin\proxy;

use luya\console\Command;
use luya\helpers\StringHelper;
use yii\base\BaseObject;
use yii\base\InvalidConfigException;
use yii\db\Connection;

/**
 * Admin Proxy Build.
 *
 * For `admin/proxy` usage see {{luya\admin\commands\ProxyController}}
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.0
 */
class ClientBuild extends BaseObject
{
    public $buildToken;

    public $requestUrl;

    public $requestCloseUrl;

    /**
     * @var string The Url to the remote storage system which provides the file returnning the file content. The url will recieved an fileId param as well as buildToken and machine params.
     */
    public $fileProviderUrl;

    /**
     * @var string The Url to the remote storage system which provides the image returnning the file content. The url will recieved an fileId param as well as buildToken and machine params.
     */
    public $imageProviderUrl;

    public $machineIdentifier;

    public $machineToken;

    public $storageFilesCount;

    /**
     * @deprecated Deprecated since version 4, will be removed in 5. No replacement.
     */
    public $optionStrict;

    public $syncRequestsCount;

    public function setOptionTable($table)
    {
        if (!empty($table)) {
            $this->_optionTable = explode(",", $table);
        }
    }

    private $_optionTable;

    public function getOptionTable()
    {
        return $this->_optionTable;
    }

    public function __construct(/**
     * @var \luya\console\Command $command object
     */
        public Command $command, /**
     * @since 2.0.0
     */
        public Connection $db,
        array $config = []
    ) {
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

    /**
     * The build config is assigned from the remote server containing all tables.
     *
     * @param array $config An array with a key tables. Example config
     * ```php
     * $config = [
     *     'tables' => [
     *         'admin_ngrest_log' => [
     *              'pks' => 1,
     *              'name' => 'admin_ngrest_log'
     *              'rows' => 2000, // the total amount of rows
     *              'fields' => ['foo', 'bar'],
     *              'offset_total' => 10,
     *         ]
     *     ]
     * ];
     * ```
     */
    public function setBuildConfig(array $config)
    {
        $this->_buildConfig = $config;

        foreach ($config['tables'] as $tableName => $tableConfig) {
            if (!empty($this->optionTable)) {
                if ($this->isSkippableTable($tableName, $this->optionTable)) {
                    continue;
                }
            }

            $schema = $this->db->getTableSchema($tableName);

            if ($schema !== null) {
                $this->_tables[$tableName] = new ClientTable($this, $tableConfig, ['db' => $this->db]);
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

    private array $_tables = [];

    /**
     * @return ClientTable[]
     */
    public function getTables()
    {
        return $this->_tables;
    }
}
