<?php

use yii\db\Migration;

/**
 * Yii Session Table
 *
 * ```php
 * session' => [
 *     'class' => 'yii\web\DbSession',
 *     'sessionTable' => 'admin_session',
 * ],
 * ```
 */
class m210422_134420_v4_update extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // cause there is `upload_timestamp` we use `updated_timestamp` instead of `updated_at`
        $this->addColumn('{{%admin_storage_file}}', 'update_timestamp', $this->integer());

        $this->addColumn('{{%admin_storage_image}}', 'created_at', $this->integer());
        $this->addColumn('{{%admin_storage_image}}', 'updated_at', $this->integer());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%admin_storage_file}}', 'update_timestamp');

        $this->dropColumn('{{%admin_storage_image}}', 'created_at');
        $this->dropColumn('{{%admin_storage_image}}', 'updated_at');
    }
}