<?php

use yii\db\Migration;

class m161220_183300_lcp_base_tables extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%admin_proxy_machine}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(255)->notNull(),
            'access_token' => $this->string(255)->notNull(),
            // max 190 chars to fit the length limit for indexes on MySQL with utf8mb4 encoding
            'identifier' => $this->string(190)->notNull()->unique(),
            'is_deleted' => $this->boolean()->defaultValue(false),
            'is_disabled' => $this->boolean()->defaultValue(false),
        ]);

        $this->createTable('{{%admin_proxy_build}}', [
            'id' => $this->primaryKey(),
            'machine_id' => $this->integer(11)->notNull(),
            'timestamp' => $this->integer(11)->notNull(),
            // max 190 chars to fit the length limit for indexes on MySQL with utf8mb4 encoding
            'build_token' => $this->string(190)->notNull()->unique(),
            'config' => $this->text()->notNull(),
            'is_complet' => $this->boolean()->defaultValue(false),
            'expiration_time' => $this->integer(11)->notNull(),
        ]);
    }

    public function safeDown()
    {
        $this->dropTable('{{%admin_proxy_machine}}');
        $this->dropTable('{{%admin_proxy_build}}');
    }
}
