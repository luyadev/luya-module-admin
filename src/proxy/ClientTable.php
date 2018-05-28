<?php

namespace luya\admin\proxy;

use Yii;
use Curl\Curl;
use yii\helpers\Json;
use yii\helpers\Console;
use yii\base\BaseObject;

/**
 * Admin Proxy comands Sync Database.
 *
 * @property \yii\db\TableSchema $schema Schema object
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.0
 */
class ClientTable extends BaseObject
{
    private $_data;
    
    /**
     * @var \luya\admin\proxy\ClientBuild
     */
    public $build;
    
    /**
     *
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
    
    public function getPks()
    {
        return $this->_data['pks'];
    }
    
    public function getName()
    {
        return $this->_data['name'];
    }
    
    public function getRows()
    {
        return $this->_data['rows'];
    }
    
    public function getFields()
    {
        return $this->_data['fields'];
    }
    
    public function getOffsetTotal()
    {
        return $this->_data['offset_total'];
    }
    
    public function isComplet()
    {
        return $this->getRows() == $this->_contentRowsCount;
    }
    
    public function getContentRows()
    {
	    throw new \ErrorException('Not implemented');
    }

    private $_contentRowsCount;

    public function getContentRowCount()
    {
    	return $this->_contentRowsCount;
    }
    
    public function syncData()
    {
	    try {
		    if (Yii::$app->db->schema instanceof \yii\db\mysql\Schema) {
			    // Disable foreign
			    Yii::$app->db->createCommand('SET SESSION FOREIGN_KEY_CHECKS = 0;')->execute();
			    Yii::$app->db->createCommand('SET SESSION UNIQUE_CHECKS = 0;')->execute();

			    $sqlMode = Yii::$app->db->createCommand('SELECT @@SQL_MODE;')->queryScalar();
			    Yii::$app->db->createCommand('SET SESSION SQL_MODE="NO_AUTO_VALUE_ON_ZERO";')->execute();
		    }

		    Yii::$app->db->createCommand()->truncateTable($this->getName())->execute();


		    $this->syncDataInternal();

	    }
	    finally {

		    if (Yii::$app->db->schema instanceof \yii\db\mysql\Schema) {
			    Yii::$app->db->createCommand('SET SESSION FOREIGN_KEY_CHECKS = 1;')->execute();
			    Yii::$app->db->createCommand('SET SESSION UNIQUE_CHECKS = 1;')->execute();

			    Yii::$app->db->createCommand('SET SESSION SQL_MODE=:sqlMode;', [':sqlMode' => $sqlMode])->execute();
		    }
	    }

    }

	private function syncDataInternal(): void
	{
		Console::startProgress(0, $this->getOffsetTotal(), 'Fetch: ' . $this->getName() . ' ');
		$this->_contentRowsCount = 0;

		$dataChunk = [];
		for ($i = 0; $i < $this->getOffsetTotal(); $i++) {
			$requestData = $this->request($i);

			if (!$requestData) {
				continue;
			}

			if ($i % 10 === 0) {
				$inserted = $this->insertData($requestData);
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

	private function request($offset)
	{
		$curl = new Curl();
		$curl->get($this->build->requestUrl, ['machine' => $this->build->machineIdentifier, 'buildToken' => $this->build->buildToken, 'table' => $this->name, 'offset' => $offset]);

		if (!$curl->error) {
			$response = Json::decode($curl->response);
			$curl->close();
			unset($curl);
			return $response;
		} else {
			$this->build->command->outputError("Error while collecting data from server: " . $curl->error_message);
		}

		return false;
	}

	/**
	 * @param $dataChunk
	 * @throws \yii\db\Exception
	 */
	private function insertData($dataChunk): int
	{
		$inserted = Yii::$app->db->createCommand()->batchInsert($this->getName(), $this->cleanUpBatchInsertFields($this->getFields()), $this->cleanUpMatchRow($dataChunk))->execute();

		return $inserted;
	}
}
