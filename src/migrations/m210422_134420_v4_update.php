<?php

use yii\db\Migration;

/**
 * Yii Session Table
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

        $this->addColumn('{{%admin_property}}', 'created_at', $this->integer());
        $this->addColumn('{{%admin_property}}', 'updated_at', $this->integer());
        $this->addColumn('{{%admin_property}}', 'is_deleted', $this->boolean()->defaultValue(false));
        $this->addColumn('{{%admin_property}}', 'sort_index', $this->integer());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%admin_storage_file}}', 'update_timestamp');

        $this->dropColumn('{{%admin_storage_image}}', 'created_at');
        $this->dropColumn('{{%admin_storage_image}}', 'updated_at');

        $this->dropColumn('{{%admin_property}}', 'created_at');
        $this->dropColumn('{{%admin_property}}', 'updated_at');
        $this->dropColumn('{{%admin_property}}', 'is_deleted');
        $this->dropColumn('{{%admin_property}}', 'sort_index');
    }
}
