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
