<?php

use yii\db\Migration;

/**
 * Class m180412_092824_user_security_columns
 */
class m180412_092824_user_security_columns extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('admin_user', 'email_verification_token', $this->string(12));
        $this->addColumn('admin_user', 'email_verification_token_expiration', $this->integer());
        $this->addColumn('admin_user', 'login_attempt', $this->integer());
        $this->addColumn('admin_user', 'login_attempt_lock_expiration', $this->integer());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('admin_user', 'email_verification_token');
        $this->dropColumn('admin_user', 'email_verification_token_expiration');
        $this->dropColumn('admin_user', 'login_attempt');
        $this->dropColumn('admin_user', 'login_attempt_lock_expiration');
    }
}
