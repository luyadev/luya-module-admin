<?php

use yii\db\Migration;

/**
 * Yii Session Table
 */
class m210510_082714_admin_config_text extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->alterColumn('{{%admin_config}}', 'value', $this->text());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->alterColumn('{{%admin_config}}', 'value', $this->string());
    }
}
