<?php

use yii\db\Migration;

/**
 * Yii Session Table
 *
 * ```php
 * session' => [
 *     'class' => 'yii\web\DbSession',
 *     'sessionTable' => 'admin_session',
 * ],
 * ```
 */
class m201112_133820_db_session_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%admin_session}}', [
            'id' => $this->char(64)->notNull(),
            'expire' => $this->integer(),
            'data' => $this->binary()
        ]);
        $this->addPrimaryKey('pk-id', '{{%admin_session}}', 'id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%admin_session}}');
    }
}
