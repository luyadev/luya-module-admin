<?php

use yii\db\Migration;

/**
 * Allow user login to store ipv6 addresses
  */
class m180527_225613_user_login_ipv6 extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->alterColumn('{{%admin_user_login}}', 'ip', $this->string(45)->notNull());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->alterColumn('{{%admin_user_login}}', 'ip', $this->string(15)->notNull());
    }
}
