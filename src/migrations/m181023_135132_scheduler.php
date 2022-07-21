<?php

use yii\db\Migration;

/**
 * Class m180723_120432_indexes
 */
class m181023_135132_scheduler extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable("{{%admin_queue}}", [
            'id' => $this->primaryKey(),
            'channel' => $this->string()->notNull(),
            'job' => $this->binary()->notNull(),
            'pushed_at' => $this->integer()->notNull(),
            'ttr' => $this->integer()->notNull(),
            'delay' => $this->integer()->notNull()->defaultValue(0),
            'priority' => $this->integer()->notNull()->unsigned()->defaultValue(1024),
            'reserved_at' => $this->integer(),
            'attempt' => $this->integer(),
            'done_at' => $this->integer(),
        ]);

        $this->createIndex('channel', '{{%admin_queue}}', 'channel');
        $this->createIndex('reserved_at', '{{%admin_queue}}', 'reserved_at');
        $this->createIndex('priority', '{{%admin_queue}}', 'priority');

        $this->createTable('{{%admin_queue_log}}', [
            'id' => $this->primaryKey(),
            'queue_id' => $this->integer()->notNull(),
            'title' => $this->string(),
            'push_timestamp' => $this->integer()->notNull(),
            'run_timestamp' => $this->integer(),
            'end_timestamp' => $this->integer(),
            'is_error' => $this->boolean()->defaultValue(false),
        ]);

        $this->createIndex('queue_id', '{{%admin_queue_log}}', 'queue_id');

        $this->createTable('{{%admin_scheduler}}', [
            'id' => $this->primaryKey(),
            'model_class' => $this->string()->notNull(),
            'primary_key' => $this->string()->notNull(),
            'target_attribute_name' => $this->string()->notNull(),
            'new_attribute_value' => $this->string()->notNull(),
            'old_attribute_value' => $this->string(),
            'schedule_timestamp' => $this->integer()->notNull(),
            'is_done' => $this->boolean()->defaultValue(false),
        ]);
    }


    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable("{{%admin_queue}}");
        $this->dropTable('{{%admin_queue_log}}');
        $this->dropTable('{{%admin_scheduler}}');
    }
}
