<?php

use yii\db\Migration;

/**
 * Class m190521_145029_admin_user_notification
 */
class m190521_145029_admin_user_notification extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%admin_user_auth_notification}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'auth_id' => $this->integer()->notNull(),
            'is_muted' => $this->boolean()->defaultValue(false),
            'model_latest_pk_value' => $this->string(),
            'model_class' => $this->string(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
        ]);

        $this->createIndex('index_admin_user_auth_notification_user_id', '{{%admin_user_auth_notification}}', ['user_id']);
        $this->createIndex('index_admin_user_auth_notification_auth_id', '{{%admin_user_auth_notification}}', ['auth_id']);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%admin_user_auth_notification}}');
    }
}
