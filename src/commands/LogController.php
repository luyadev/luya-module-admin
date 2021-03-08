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
     * Holds the minium timestamp to keep entries
     * @var integer
     */
    private $_referenceTimestamp;
    /**
     * Holds the old entries number found during age check 
     * @var integer
     */
    private $_oldRowsCount;

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
        if ( $this->_validateRows() && $this->_validateYears() && $this->_validateTables($logTableName)) {
            // clean old log entries for each log tbale provided
            $this->_referenceTimestamp = strtotime(sprintf("-%s year", $this->years));
            foreach ($this->_dbLogTables as $logTableName=>$timestampField){
                // output header
                $this->outputInfo( sprintf("\nChecking log table %s", $logTableName ));
                $this->outputInfo( str_repeat("-", 80) );                
                $this->_doClean($logTableName, $timestampField);
            }
        }
    } // END actionCleanup()

    private function _doClean($logTableName, $timestampField){
        if ( $this->_moreThanMinimumRowsFound($logTableName) && $this->_olderThanMiniumYearsFound($logTableName, $timestampField) && $this->_removalConfirmed($logTableName)) {
            $removed = $this->dryRun ?  $this->_oldRowsCount : Yii::$app->db->createCommand()->delete("{{%$logTableName}}", "$timestampField < :timestampLimit", [
                ':timestampLimit' => $this->_referenceTimestamp,
            ])->execute();
            if ($removed > 0) {
               $this->outputSuccess(sprintf("%s entries removed", $removed));
            }
            else {
               $this->outputInfo("No log entries renoved.");
            }
        }
    } // END _doClean()

    private function _removalConfirmed($logTableName){
        if ($this->interactive && !$this->confirm("Do you want to delete the extra entries from $logTableName table?")) {
            $this->outputError("Log entries clean-up aborted.");
            return false;
        } 
        return true;
    }
    
    private function _validateRows(){
        // validate the minimum rows option
        $this->rows  = (int)$this->rows;
        if ($this->rows < 0){
            $this->outputError("Minimum rows to keep should be positive.");
            return false;
        }
        return true;
    }

    private function _validateYears(){
        // validate the years option
        $this->years = (int)$this->years;
        if ($this->years< 0){
            $this->outputError("Minimum Years to keep should be positive.");
            return false;
        }
        return true;
    }

    private function _validateTables($logTableName){
        // validate the log table names argument
        if ( strtoupper($logTableName)!='ALL'){
            // extarct the table names and check them towards the known list of log tables
            $logTableList = explode(',', $logTableName);
            $wrongTableNames = array_diff($logTableList, array_keys($this->_dbLogTables));
            if ($wrongTableNames) {
                $this->outputInfo("Please specifiy a valid log table to clean among below list:");
                $this->outputInfo("  - " . join("\n  - ", array_keys($this->_dbLogTables)));
                $this->outputError(sprintf("Error. Invalid table name '%s'", join("', '", $wrongTableNames) ));
                return false;
            }
            else {
                // keep oonly provided table names
                $this->_dbLogTables = array_flip(array_intersect(array_flip($this->_dbLogTables), $logTableList));
            }
        }
        return true;
    }

    private function _moreThanMinimumRowsFound($logTableName){
         // check entries count towards minimum threshold
        $totalRowsCount = Yii::$app->db->createCommand("SELECT count(*) as count FROM {{%$logTableName}}")->queryScalar();
        $this->output(sprintf("Total entries found : $totalRowsCount (minimum to keep %s)",  $this->rows));
        if ($totalRowsCount < $this->rows) {
            $this->outputInfo("Log entries do not execeed minium to keep.");
            return false;
        }
        return true;
    }

    private function _olderThanMiniumYearsFound($logTableName, $timestampField){
        //check entries age towards minimum years threshold
        $this->_oldRowsCount = Yii::$app->db->createCommand("SELECT count(*) as count FROM {{%$logTableName}} WHERE $timestampField < :timestampLimit", [
            ':timestampLimit' => $this->_referenceTimestamp,
        ])->queryScalar();

        $this->output(sprintf("Total old entries : $this->_oldRowsCount (reference date %s)",  date('d-M-Y H:i:s', $this->_referenceTimestamp)));

        if ($this->_oldRowsCount == 0) {
            $this->outputInfo("Log entries are  not old enough to delete.");
            return false;
        }
        return true;
    }

} // END LogController class