<?php

use yii\db\Migration;

/**
 * Class m180723_120432_indexes
 */
class m181113_120432_user_index extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // admin_user
        $this->createIndex('index_api_last_activity_id', '{{%admin_user}}', ['api_last_activity', 'id']);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('index_api_last_activity_id', '{{%admin_user}}');
    }
}
