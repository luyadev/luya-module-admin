<?php

namespace luya\admin\ngrest\base;

use yii\db\ActiveRecordInterface;

/**
 * Interface For NgRest Model.
 *
 * Each Active-Record which is used as an NgRest Configuration must implement this Interface.
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.0
 */
interface NgRestModelInterface extends ActiveRecordInterface
{
    /**
     * Defines the base inline configuration for the current Model.
     *
     * @param \luya\admin\ngrest\ConfigBuilder $config ConfigBuilder Object
     */
    public function ngRestConfig($config);

    /**
     * Defines the Api Endpoint for the current Active Record model.
     *
     * @return string
     */
    public static function ngRestApiEndpoint();

    /**
     * Whether current model is in ngrest context or not
     *
     * @return boolean
     */
    public function getIsNgRestContext();

    /**
     * The NgRestFind is used when performing the crud list index overivew. You
     * can override this method in order to hide data from the ngRestFind command
     * which populates all data from the database.
     *
     * An example for hidding deleted news posts from the crud list:
     *
     * ```php
     * public static function ngRestFind()
     * {
     *     return parent::ngRestFind()->where(['is_deleted' => 0]);
     * }
     * ```
     *
     * > This method will taken for all internal *NOT API USER* related calls. So assuming an API user makes request to the APi
     * > it will use find() instead of ngRestFind(). If a logged in user will make a request to an API it will take ngRestFind().
     *
     * > Also this methid is used for the admin scheduler, see {{luya\admin\models\Scheduler::triggerJob()}}.
     *
     * @see {{yii\db\ActiveRecord::find()}}
     * @return NgRestActiveQuery
     */
    public static function ngRestFind();
}
