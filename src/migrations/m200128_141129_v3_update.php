<?php

use yii\db\Migration;

/**
 * Class m190521_145029_admin_user_notification
 */
class m200128_141129_v3_update extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // user login agent
        $this->addColumn('{{%admin_user_login}}', 'user_agent', $this->string());

        // user device
        $this->createTable('{{%admin_user_device}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'auth_key' => $this->string(190)->unique()->notNull(),
            'user_agent' => $this->string()->notNull(),
            'user_agent_checksum' => $this->string()->notNull(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
        ]);

        $this->createIndex('index_admin_user_device_user_id', '{{%admin_user_device}}', ['user_id']);
        // user 2fa
        $this->addColumn('{{%admin_user}}', 'login_2fa_enabled', $this->boolean()->defaultValue(false));
        $this->addColumn('{{%admin_user}}', 'login_2fa_secret', $this->string());
        $this->addColumn('{{%admin_user}}', 'login_2fa_backup_key', $this->string());

        // queue
        $this->createTable('{{%admin_queue_log_error}}', [
            'id' => $this->primaryKey(),
            'queue_log_id' => $this->integer()->notNull(),
            'message' => $this->text(),
            'code' => $this->string(),
            'trace' => $this->text(),
            'file' => $this->text(),
            'line' => $this->string(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
        ]);

        $this->createIndex('index_admin_queue_log_error_queue_log_id', '{{%admin_queue_log_error}}', 'queue_log_id');

        // user login lockout
        $this->createTable('{{%admin_user_login_lockout}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'ip' => $this->string(45)->notNull(),
            'attempt_count' => $this->integer()->defaultValue(0),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
        ]);

        $this->createIndex('index_admin_user_login_lockout_user_id', '{{%admin_user_login_lockout}}', 'user_id');

        // password restore link
        $this->addColumn('{{%admin_user}}', 'password_verification_token', $this->string(40));
        $this->addColumn('{{%admin_user}}', 'password_verification_token_timestamp', $this->integer());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // user login UA
        $this->dropColumn('{{%admin_user_login}}', 'user_agent');

        // user device
        $this->dropTable('{{%admin_user_device}}');

        // user 2fa
        $this->dropColumn('{{%admin_user}}', 'login_2fa_enabled');
        $this->dropColumn('{{%admin_user}}', 'login_2fa_secret');
        $this->dropColumn('{{%admin_user}}', 'login_2fa_backup_key');

        // queue error log
        $this->dropTable('{{%admin_queue_log_error}}');

        // user lockout
        $this->dropTable('{{%admin_user_login_lockout}}');

        // password restore link
        $this->dropColumn('{{%admin_user}}', 'password_verification_token');
        $this->dropColumn('{{%admin_user}}', 'password_verification_token_timestamp');
    }
}
