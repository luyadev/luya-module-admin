<?php

namespace luya\admin\commands;

use Yii;
use luya\console\Command;

/**
 * LUYA Admin Log command.
 *
 * Cleanup ngrest and cms log data.
 *
 * @author
 * @since
 */
class LogController extends Command
{

    /**
     * @var boolean Whether to perform a dry run or not.
     */
    public $dryRun = false;

    /**
     * @var integer The minimum number of rows to keep.
     */
    public $rows = 5000;

    /**
     * @var integer The minimum age of log entries (in years) to keep.
     */
    public $years = 2;

    /**
     * @var array The list of log tables with timestamp field definition.
     */
    private $_dbLogTables = [
        'admin_ngrest_log' => 'timestamp_create',
        'cms_log' => 'timestamp',
    ];

    /**
     * @inheritdoc
     */
    public function options($actionID)
    {
        parent::options($actionID);
        return ['logTable', 'dryRun', 'rows', 'years', 'interactive'];
    }

    /**
     * Clean up logs older than a given threshold.
     * @param the log tables to clean separated by comma
     */
    public function actionCleanup($logTableName)
    {

        // validate the minimum rows option
        $this->rows  = (int)$this->rows;
        if ($this->rows < 0){
            return $this->outputError("Minimum rows to keep should be positive.");
        }

        // validate the years option
        $this->years = (int)$this->years;
        if ($this->years< 0){
            return $this->outputError("Minimum Years to keep should be positive.");
        }

        // validate the log table names argument
        if ( strtoupper($logTableName)=='ALL'){
            $logTableList = array_keys($this->_dbLogTables);
        }
        else {
            // extarct the table names and check them towards the known list of log tables
            $logTableList = explode(',', $logTableName);
            foreach ($logTableList as $name){
                if (!isset($this->_dbLogTables[$name])){
                    $this->outputInfo("Please specifiy a valid log table to clean among below list:");
                    foreach ($this->_dbLogTables as $table=>$timestampField){
                        $this->outputInfo("\t- $table");
                    }
                    return $this->outputError("Error. Invalid table name '$name'");
                }
            }
        }

        // clean old log entries for each log tbale provided
        foreach ($logTableList as $logTableName){
            // get the timestamp field form the tables definition
            $timestampField = $this->_dbLogTables[$logTableName];

            // output header
            $this->outputInfo( sprintf("\nChecking log table %s", $logTableName ));
            $this->outputInfo( str_repeat("-", 80) );

            // check entries count towards minimum threshold
            $totalRowsCount = Yii::$app->db->createCommand("SELECT count(*) as count FROM {{%$logTableName}}")->queryScalar();
            $this->output(sprintf("Total entries found : $totalRowsCount (minimum to keep %s)",  $this->rows));
            if ($totalRowsCount < $this->rows) {
                $this->outputInfo("Log entries do not execeed minium to keep.");
                continue;
            }

            //check entries age towards minimum years threshold
            $referenceTimestamp = strtotime(sprintf("-%s year", $this->years));
            $oldRowsCount = Yii::$app->db->createCommand("SELECT count(*) as count FROM {{%$logTableName}} WHERE $timestampField < :timestampLimit", [
                ':timestampLimit' => $referenceTimestamp,
            ])->queryScalar();

            $this->output(sprintf("Total old entries : $oldRowsCount (reference date %s)",  date('d-M-Y H:i:s', $referenceTimestamp)));

            if ($oldRowsCount == 0) {
                $this->outputInfo("Log entries are  not old enough to delete.");
                continue;
            }

            if ($this->interactive) {
                if (!$this->confirm("Do you want to delete the extra entries from $logTableName table?")) {
                    $this->outputError("Log entries clean-up aborted.");
                    continue;
                }
            }

            $removed = $this->dryRun? $oldRowsCount : Yii::$app->db->createCommand()->delete("{{%$logTableName}}", "$timestampField < :timestampLimit", [
                ':timestampLimit' => $referenceTimestamp,
            ])->execute();

            if ($removed) {
                $this->outputSuccess(sprintf("%s entries removed", $removed));
            }
            else {
                $this->outputInfo("No log entries renoved.");
            }
        } // END foreach
    } // END actionCleanup()
} // END LogController class