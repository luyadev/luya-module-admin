<?php

use yii\db\Migration;

/**
 * Class m180619_134519_indexes
 */
class m180619_134519_indexes extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createIndex('admin_storage_image_index1', '{{%admin_storage_image}}', ['file_id']);
        $this->createIndex('admin_storage_file_index1', '{{%admin_storage_file}}', ['folder_id', 'is_hidden', 'is_deleted', 'name_original']);
        $this->createIndex('admin_storage_file_index2', '{{%admin_storage_file}}', ['is_deleted', 'id']);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('admin_storage_image_index1', '{{%admin_storage_image}}');
        $this->dropIndex('admin_storage_file_index1', '{{%admin_storage_file}}');
        $this->dropIndex('admin_storage_file_index2', '{{%admin_storage_file}}');
    }
}
