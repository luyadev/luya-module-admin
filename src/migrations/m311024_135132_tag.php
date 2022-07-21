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
        $this->addColumn('{{%admin_tag}}', 'translation', $this->text());
        $this->addPrimaryKey('admin_tag_relation_pk', '{{%admin_tag_relation}}', ['tag_id', 'table_name', 'pk_id']);
    }


    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%admin_tag}}', 'translation');
        $this->dropPrimaryKey('admin_tag_relation_pk', '{{%admin_tag_relation}}');
    }
}
