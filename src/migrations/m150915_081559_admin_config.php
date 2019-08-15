<?php

use yii\db\Migration;

class m150915_081559_admin_config extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%admin_config}}', [
            'name' => $this->string(80),
            'value' => $this->string(255)->notNull(),
        ]);
        
        $this->addPrimaryKey('name', '{{%admin_config}}', 'name');
    }

    public function safeDown()
    {
        $this->dropTable('{{%admin_config}}');
    }
}
