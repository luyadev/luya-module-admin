<?php

namespace luya\admin\proxy;

use Curl\Curl;
use Yii;
use yii\base\BaseObject;
use yii\db\Connection;
use yii\db\Exception;
use yii\helpers\Console;
use yii\helpers\Json;

/**
 * Prepare Client Tables
 *
 * For `admin/proxy` usage see {{luya\admin\commands\ProxyController}}
 *
 * @property \yii\db\TableSchema $schema Schema object
 * @property Connection $db Database connection. By default `Yii::$app->db` will be used.
 *
 * @author Basil Suter <basil@nadar.io>
 * @author Bennet Klarhölter <boehsermoe@me.com>
 *
 * @since 1.0.0
 */
class ClientTable extends BaseObject
{
    public const LARGE_TABLE_PROMPT = 10000;

    /**
     * @param ClientBuild $build
     * @param array $_data
     * @param array $config
     */
    public function __construct(public ClientBuild $build, private array $_data, array $config = [])
    {
        parent::__construct($config);
    }

    /**
     * @since 2.0.0
     */
    private ?\yii\db\Connection $_db = null;

    /**
     * @return Connection
     * @since 2.0.0
     */
    public function getDb()
    {
        if (!$this->_db) {
            $this->setDb(Yii::$app->db);
        }

        return $this->_db;
    }

    /**
     * @param Connection $db
     *
     * @since 2.0.0
     */
    public function setDb(Connection $db)
    {
        $this->_db = $db;
    }

    private $_schema;

    public function getSchema()
    {
        if ($this->_schema === null) {
            $this->_schema = $this->getDb()->getTableSchema($this->getName());
        }

        return $this->_schema;
    }

    public function getColumns()
    {
        return $this->schema->getColumnNames();
    }

    /**
     * @return array
     */
    public function getPks()
    {
        return $this->_data['pks'];
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->_data['name'];
    }

    /**
     * @return string|integer
     */
    public function getRows()
    {
        return $this->_data['rows'];
    }

    /**
     * @return array
     */
    public function getFields()
    {
        return $this->_data['fields'];
    }

    /**
     * @return integer
     */
    public function getOffsetTotal()
    {
        return $this->_data['offset_total'];
    }

    /**
     * @return bool
     */
    public function isComplet()
    {
        return $this->getRows() == $this->getContentRowCount();
    }

    private $_contentRowsCount;

    /**
     * @return integer
     */
    public function getContentRowCount()
    {
        return $this->_contentRowsCount;
    }

    /**
     * Sync the data from remote table to local table.
     *
     * @throws \yii\db\Exception
     */
    public function syncData()
    {
        if (Yii::$app->controller->interactive && $this->getRows() > self::LARGE_TABLE_PROMPT) {
            if (Console::confirm("{$this->getName()} has {$this->getRows()} entries. Do you want continue table sync?", true) === false) {
                return;
            }
        }

        $sqlMode = $this->prepare();

        try {
            $this->getDb()->createCommand()->truncateTable($this->getName())->execute();

            $this->syncDataInternal();
        } finally {
            $this->cleanup($sqlMode);
        }
    }

    /**
     * Prepare database for data sync and set system variables.
     *
     * Disable the foreign key and unique check. Also set the sql mode to "NO_AUTO_VALUE_ON_ZERO".
     * Currently only for MySql and MariaDB.
     *
     * @return false|null|string The old sql mode.
     * @throws \yii\db\Exception
     * @since 1.2.1
     */
    protected function prepare()
    {
        $sqlMode = null;

        if ($this->getDb()->schema instanceof \yii\db\mysql\Schema) {
            $this->getDb()->createCommand('SET FOREIGN_KEY_CHECKS = 0;')->execute();
            $this->getDb()->createCommand('SET UNIQUE_CHECKS = 0;')->execute();

            $sqlMode = $this->getDb()->createCommand('SELECT @@SQL_MODE;')->queryScalar();
            $this->getDb()->createCommand('SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";')->execute();
        }

        return $sqlMode;
    }

    /**
     * Revert database system variables.
     *
     * Enable the foreign key and unique check. Also set the sql mode to the given value.
     * Currently only for MySql and MariaDB.
     *
     * @param $sqlMode string|null The old sql mode value from @see \luya\admin\proxy\ClientTable::prepare()
     * @see \luya\admin\proxy\ClientTable::prepare()
     * @throws \yii\db\Exception
     * @since 1.2.1
     */
    protected function cleanup($sqlMode)
    {
        if ($this->getDb()->schema instanceof \yii\db\mysql\Schema) {
            try {
                $this->getDb()->createCommand('SELECT CONNECTION_ID()')->execute();
            } catch (Exception $ex) {
                throw new \luya\Exception('Connection lost. Server has gone away?');
            }

            $this->getDb()->createCommand('SET FOREIGN_KEY_CHECKS = 1;')->execute();
            $this->getDb()->createCommand('SET UNIQUE_CHECKS = 1;')->execute();

            if ($sqlMode !== null) {
                $this->getDb()->createCommand('SET SQL_MODE=:sqlMode;', [':sqlMode' => $sqlMode])->execute();
            }
        }
    }

    /**
     * Start the data sync.
     *
     * Fetch the data from remote url and write into the database.
     *
     * @throws \yii\db\Exception
     * @see \luya\admin\proxy\ClientBuild::$syncRequestsCount
     * @since 1.2.1
     */
    private function syncDataInternal()
    {
        Console::startProgress(0, $this->getOffsetTotal(), 'Fetch: ' . $this->getName() . ' ');
        $this->_contentRowsCount = 0;

        $dataChunk = [];
        for ($i = 0; $i < $this->getOffsetTotal(); ++$i) {
            $requestData = $this->request($i);

            if (!$requestData) {
                continue;
            }

            if (0 === $i % $this->build->syncRequestsCount) {
                $inserted = $this->insertData($dataChunk);
                $this->_contentRowsCount += $inserted;
                $dataChunk = [];
            }

            Console::updateProgress($i + 1, $this->getOffsetTotal());

            $dataChunk = array_merge($requestData, $dataChunk);
            gc_collect_cycles();
        }

        if (!empty($dataChunk)) {
            $this->insertData($dataChunk);
        }

        Console::endProgress();
    }

    /**
     * Send request for this table and return the JSON data.
     *
     * @param $offset
     * @return bool|mixed JSON response, false if failed.
     */
    private function request($offset)
    {
        $curl = new Curl();
        $curl->get($this->build->requestUrl, [
            'machine' => $this->build->machineIdentifier,
            'buildToken' => $this->build->buildToken,
            'table' => $this->name,
            'offset' => $offset
        ]);

        if (!$curl->error) {
            $response = Json::decode($curl->response);
            $curl->close();
            unset($curl);

            return $response;
        } else {
            $this->build->command->outputError('Error while collecting data from server: ' . $curl->error_message);
        }

        return false;
    }

    /**
     * Write the given data to the database.
     *
     * @param array $data
     * @throws \yii\db\Exception
     * @return int
     */
    private function insertData(array $data)
    {
        $inserted = $this->getDb()->createCommand()->batchInsert(
            $this->getName(),
            $this->cleanUpBatchInsertFields($this->getFields()),
            $this->cleanUpMatchRow($data)
        )->execute();

        return $inserted;
    }

    /**
     * Clean Up matching Rows
     *
     * @param array $row
     * @return array
     */
    protected function cleanUpMatchRow(array $row)
    {
        $data = [];
        foreach ($row as $key => $item) {
            foreach ($item as $field => $value) {
                if (in_array($field, $this->getColumns())) {
                    $data[$key][$field] = $value;
                }
            }
        }

        return $data;
    }

    /**
     * Clean Up Batch Insert Fields
     *
     * @param array $fields
     * @return array
     */
    protected function cleanUpBatchInsertFields(array $fields)
    {
        $data = [];
        foreach ($fields as $field) {
            if (in_array($field, $this->getColumns())) {
                $data[] = $field;
            }
        }

        return $data;
    }
}
