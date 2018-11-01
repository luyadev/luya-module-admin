<?php

use yii\db\Migration;

/**
 * Class m180723_120432_indexes
 */
class m311024_135132_tag extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('admin_tag', 'translation', $this->text());
    }
    

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('admin_tag', 'translation');
    }
}
