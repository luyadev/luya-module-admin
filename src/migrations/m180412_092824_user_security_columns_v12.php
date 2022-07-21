<?php

use yii\db\Migration;

/**
 * Class m180412_092824_user_security_columns
 */
class m180412_092824_user_security_columns_v12 extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // due to a bug in 1.1.1.2 release an unwanted migration has been commited, now the columns have to be checked and dropped if needed.

        // The column name `email_verification_token_expiration` which has been renamed in 1.2 branch - just drop without re-creation.
        $this->dropIfExists('email_verification_token_expiration');

        $this->dropIfExists('email_verification_token');
        $this->dropIfExists('email_verification_token_timestamp');
        $this->dropIfExists('login_attempt');
        $this->dropIfExists('login_attempt_lock_expiration');

        $this->addColumn('{{%admin_user}}', 'email_verification_token', $this->string(40));
        $this->addColumn('{{%admin_user}}', 'email_verification_token_timestamp', $this->integer());
        $this->addColumn('{{%admin_user}}', 'login_attempt', $this->integer());
        $this->addColumn('{{%admin_user}}', 'login_attempt_lock_expiration', $this->integer());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIfExists('email_verification_token_expiration');

        $this->dropColumn('{{%admin_user}}', 'email_verification_token');
        $this->dropColumn('{{%admin_user}}', 'email_verification_token_timestamp');
        $this->dropColumn('{{%admin_user}}', 'login_attempt');
        $this->dropColumn('{{%admin_user}}', 'login_attempt_lock_expiration');
    }

    /**
     * Check if a given column exists.
     *
     * @param string $name
     */
    private function hasColumn($name)
    {
        return $this->db->schema->getTableSchema('{{%admin_user}}')->getColumn($name);
    }

    /**
     * Drop the column if exists.
     *
     * @param string $name
     */
    private function dropIfExists($name)
    {
        if ($this->hasColumn($name)) {
            $this->dropColumn('{{%admin_user}}', $name);
        }
    }
}
