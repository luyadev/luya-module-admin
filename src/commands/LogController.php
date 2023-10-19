<?php

namespace luya\admin\commands;

use luya\console\Command;
use Yii;

/**
 * LUYA Admin Logdata cleanup command.
 *
 * This database maintenance command will cleanup ngrest and cms log data.
 *
 * USAGE
 *
 * ```sh
 * ./vendor/bin/luya admin/log/cleanup <logTableName> [--years=YYY] [--rows=XXX] [--interactive=0] [--dry-run]
 * ```
 *
 * The  logTableName is a comma separated string of db tables to cleanup. If `all`
 * is provided, all available log tables will be cleanedup. Entries older than
 * YYY years will be removed if there is at least XXX rows in the log table.
 *
 * The command will ask for user confirmation prior to actual entries removal from
 * each log table. In order to perform a silent cleanup (useful in a cron job
 * for instance), use the `--interactive=0` option.
 *
 * A dry run can be performed to see how many entries will be removed using the
 * `--dry-run` option.
 *
 *
 * EXAMPLES
 *
 * To cleanup all available log tables use
 *
 * ```sh
 * ./vendor/bin/luya admin/log/cleanup all
 * ```
 *
 * In order to cleanup all data without year or amount persistence use
 *
 *```sh
 * ./vendor/bin/luya admin/log/cleanup all --rows=0 --years=0
 * ```
 *
 * To see how many entries will be deleted from `admin_ngrest_log` table only
 * when more than 2000 entries are present and for those older than 2 years, use
 *
 *```sh
 * ./vendor/bin/luya admin/log/cleanup admin_ngrest_log --dry-run --rows=2000 --years=2 --interactive=0
 * ```
 *
 * @author Rochdi Bazine <rochdi80tn@gmail.com>
 * @since 4.0.0
 */
class LogController extends Command
{
    public const ALL_TABLES = 'ALL';

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
    private array $_dbLogTables = [
        'admin_ngrest_log' => 'timestamp_create',
        'cms_log' => 'timestamp',
        'admin_user_login' => 'timestamp_create',
    ];

    /**
     * @var integer Holds the minium timestamp to keep entries
     */
    private $_referenceTimestamp;

    /**
     *
     * @var integer Holds the old entries number found during age check
     */
    private $_oldRowsCount;

    /**
     * @inheritdoc
     */
    public function options($actionID)
    {
        return array_merge(['dryRun', 'rows', 'years'], parent::options($actionID));
    }

    /**
     * Clean up logs older than a given threshold.
     *
     * @param string the log tables to clean separated by comma if `all` is provided, all available log tables will be cleanedup
     */
    public function actionCleanup($logTableName)
    {
        if ($this->validateRows() && $this->validateYears() && $this->validateTables($logTableName)) {
            // clean old log entries for each log tbale provided
            $this->_referenceTimestamp = strtotime(sprintf("-%s year", $this->years));
            foreach ($this->_dbLogTables as $logTableName => $timestampField) {
                // output header
                $this->outputInfo(sprintf("\nChecking log table %s", $logTableName));
                $this->outputInfo(str_repeat("-", 80));
                $this->doClean($logTableName, $timestampField);
            }
        }
    }

    /**
     * Cleanup the table name with the given time
     *
     * @param string $logTableName
     * @param integer $timestampField
     */
    private function doClean($logTableName, $timestampField)
    {
        if ($this->moreThanMinimumRowsFound($logTableName) && $this->olderThanMiniumYearsFound($logTableName, $timestampField) && $this->removalConfirmed($logTableName)) {
            $removed = $this->dryRun ? $this->_oldRowsCount : Yii::$app->db->createCommand()->delete("{{%$logTableName}}", "$timestampField < :timestampLimit", [
                ':timestampLimit' => $this->_referenceTimestamp,
            ])->execute();
            if ($removed > 0) {
                $this->outputSuccess(sprintf("%s entries %sremoved", $removed, $this->dryRun ? 'will be ' : ''));
            } else {
                $this->outputInfo("No log entries removed.");
            }
        }
    }

    /**
     * Confirm
     *
     * @param string $logTableName
     * @return boolean
     */
    private function removalConfirmed($logTableName)
    {
        if ($this->interactive && !$this->confirm("Do you want to delete the extra entries from \"$logTableName\" table?")) {
            $this->outputError("Log entries clean-up aborted.");
            return false;
        }

        return true;
    }

    /**
     * validate the minimum rows option
     *
     * @return boolean
     */
    private function validateRows()
    {
        $this->rows = (int) $this->rows;
        if ($this->rows < 0) {
            $this->outputError("Minimum rows to keep should be positive.");
            return false;
        }

        return true;
    }

    /**
     * validate the years option
     *
     * @return boolean
     */
    private function validateYears()
    {
        $this->years = (int) $this->years;
        if ($this->years < 0) {
            $this->outputError("Minimum Years to keep should be positive.");
            return false;
        }

        return true;
    }

    /**
     * Validate the log table name whether its in the list of valid tables
     *
     * @param string $logTableName
     * @return boolean
     */
    private function validateTables($logTableName)
    {
        $logTableName = (string) $logTableName;
        if (strtoupper($logTableName) !== self::ALL_TABLES) {
            // extarct the table names and check them towards the known list of log tables
            $logTableList = explode(',', $logTableName);
            $wrongTableNames = array_diff($logTableList, array_keys($this->_dbLogTables));
            if ($wrongTableNames) {
                $this->outputInfo("Please specifiy a valid log table to clean among below list:");
                $this->outputInfo("  - " . implode("\n  - ", array_keys($this->_dbLogTables)));
                $this->outputError(sprintf("Error. Invalid table name '%s'", implode("', '", $wrongTableNames)));
                return false;
            } else {
                // keep only provided table names
                $this->_dbLogTables = array_flip(array_intersect(array_flip($this->_dbLogTables), $logTableList));
            }
        }

        return true;
    }

    /**
     * check entries count towards minimum threshold
     *
     * @param string $logTableName
     * @return boolean
     */
    private function moreThanMinimumRowsFound($logTableName)
    {
        $totalRowsCount = Yii::$app->db->createCommand("SELECT count(*) as count FROM {{%$logTableName}}")->queryScalar();
        $this->output(sprintf("Total entries found : $totalRowsCount (minimum to keep %s)", $this->rows));
        if ($totalRowsCount < $this->rows) {
            $this->outputInfo("Log entries do not execeed minium to keep.");
            return false;
        }

        return true;
    }

    /**
     * check entries age towards minimum years threshold
     *
     * @param string $logTableName
     * @param integer $timestampField
     * @return boolean
     */
    private function olderThanMiniumYearsFound($logTableName, $timestampField)
    {
        $this->_oldRowsCount = Yii::$app->db->createCommand("SELECT count(*) as count FROM {{%$logTableName}} WHERE $timestampField < :timestampLimit", [
            ':timestampLimit' => $this->_referenceTimestamp,
        ])->queryScalar();

        $this->output(sprintf("Total old entries : $this->_oldRowsCount (reference date %s)", date('d-M-Y H:i:s', $this->_referenceTimestamp)));

        if ($this->_oldRowsCount == 0) {
            $this->outputInfo("Log entries are not old enough to delete.");
            return false;
        }

        return true;
    }
}
