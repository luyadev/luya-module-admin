<?php

use yii\db\Migration;

/**
 * Class m190327_140741_auth_pool_field
 */
class m190327_140741_auth_pool_field extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%admin_auth}}', 'pool', $this->string());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%admin_auth}}', 'pool');
    }
}
