<?php

use yii\db\Migration;

/**
 * Class m190508_142342_admin_user_api_request
 */
class m190508_142342_admin_user_api_request extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%admin_user}}', 'is_request_logger_enabled', $this->boolean()->defaultValue(false));

        $this->createTable('{{%admin_user_request}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'timestamp' => $this->integer()->notNull(),
            'request_url' => $this->string()->notNull(),
            'request_method' => $this->string()->notNull(),
            'response_time' => $this->integer()->notNull(),
        ]);

        $this->createIndex('index_admin_user_admin_user_id', '{{%admin_user_request}}', ['user_id']);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%admin_user}}', 'is_request_logger_enabled');

        $this->dropIndex('index_admin_user_admin_user_id', '{{%admin_user_request}}');

        $this->dropTable('{{%admin_user_request}}');
    }
}
