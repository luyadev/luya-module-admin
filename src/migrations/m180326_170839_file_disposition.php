<?php

use yii\db\Migration;

/**
 * Class m180326_170839_file_disposition
 */
class m180326_170839_file_disposition extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn('{{%admin_storage_file}}', 'inline_disposition', $this->boolean()->defaultValue(false));
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropColumn('{{%admin_storage_file}}', 'inline_disposition');
    }
}
