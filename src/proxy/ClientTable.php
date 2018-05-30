<?php

namespace luya\admin\proxy;

use Curl\Curl;
use Yii;
use yii\base\BaseObject;
use yii\helpers\Console;
use yii\helpers\Json;

/**
 * Admin Proxy comands Sync Database.
 *
 * @property \yii\db\TableSchema $schema Schema object
 *
 * @author Basil Suter <basil@nadar.io>
 * @author Bennet Klarhölter <boehsermoe@me.com>
 *
 * @since 1.0.0
 */
class ClientTable extends BaseObject
{
    const LARGE_TABLE_PROMPT = 10000;

    private $_data;

    /**
     * @var \luya\admin\proxy\ClientBuild
     */
    public $build;

    /**
     * @param ClientBuild $build
     * @param array $data
     * @param array $config
     */
    public function __construct(ClientBuild $build, array $data, array $config = [])
    {
        $this->build = $build;
        $this->_data = $data;
        parent::__construct($config);
    }

    private $_schema;

    public function getSchema()
    {
        if ($this->_schema === null) {
            $this->_schema = Yii::$app->db->getTableSchema($this->getName());
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
            Yii::$app->db->createCommand()->truncateTable($this->getName())->execute();

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
    private function prepare()
    {
        $sqlMode = null;

        if (Yii::$app->db->schema instanceof \yii\db\mysql\Schema) {
            Yii::$app->db->createCommand('SET FOREIGN_KEY_CHECKS = 0;')->execute();
            Yii::$app->db->createCommand('SET UNIQUE_CHECKS = 0;')->execute();

            $sqlMode = Yii::$app->db->createCommand('SELECT @@SQL_MODE;')->queryScalar();
            Yii::$app->db->createCommand('SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";')->execute();
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
    private function cleanup($sqlMode)
    {
        if (Yii::$app->db->schema instanceof \yii\db\mysql\Schema) {
            Yii::$app->db->createCommand('SET FOREIGN_KEY_CHECKS = 1;')->execute();
            Yii::$app->db->createCommand('SET UNIQUE_CHECKS = 1;')->execute();

            if ($sqlMode !== null) {
                Yii::$app->db->createCommand('SET SQL_MODE=:sqlMode;', [':sqlMode' => $sqlMode])->execute();
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
     * @param $data
     * @throws \yii\db\Exception
     * @return int
     */
    private function insertData($data)
    {
        $inserted = Yii::$app->db->createCommand()->batchInsert(
            $this->getName(),
            $this->cleanUpBatchInsertFields($this->getFields()),
            $this->cleanUpMatchRow($data)
        )->execute();

        return $inserted;
    }

    protected function cleanUpMatchRow($row)
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

    protected function cleanUpBatchInsertFields($fields)
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
