<?php

use yii\db\Migration;

/**
 * Class m180723_120432_indexes
 */
class m180723_120432_indexes extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // admin_auth
        $this->createIndex('index_route', '{{%admin_auth}}', ['route']);
        $this->createIndex('index_api', '{{%admin_auth}}', ['api']);

        // admin_config
        $this->createIndex('index_name', '{{%admin_config}}', ['name']);

        // admin_group_auth
        $this->createIndex('index_admin_group_auth_group_id', '{{%admin_group_auth}}', ['group_id']);
        $this->createIndex('index_auth_id', '{{%admin_group_auth}}', ['auth_id']);
        $this->createIndex('index_group_id_auth_id', '{{%admin_group_auth}}', ['group_id', 'auth_id']);

        // admin_lang
        $this->createIndex('index_short_code', '{{%admin_lang}}', ['short_code']);

        // admin_ngrest_log
        $this->createIndex('index_admin_ngrest_log_user_id', '{{%admin_ngrest_log}}', ['user_id']);

        // admin_property
        $this->createIndex('index_var_name', '{{%admin_property}}', ['var_name']);
        $this->createIndex('index_class_name', '{{%admin_property}}', ['class_name']);

        // admin_proxy_build
        $this->createIndex('index_machine_id', '{{%admin_proxy_build}}', ['machine_id']);
        $this->createIndex('index_build_token', '{{%admin_proxy_build}}', ['build_token']);

        // admin_proxy_machine
        $this->createIndex('index_access_token', '{{%admin_proxy_machine}}', ['access_token']);
        $this->createIndex('index_identifier', '{{%admin_proxy_machine}}', ['identifier']);
        $this->createIndex('index_is_deleted', '{{%admin_proxy_machine}}', ['is_deleted']);
        $this->createIndex('index_identifier_is_deleted', '{{%admin_proxy_machine}}', ['identifier', 'is_deleted']);

        // admin_storage_file
        $this->createIndex('index_upload_user_id', '{{%admin_storage_file}}', ['upload_user_id']);
        $this->createIndex('index_id_hash_name_is_deleted', '{{%admin_storage_file}}', ['id', 'hash_name', 'is_deleted']);
        $this->createIndex('index_name_new_compound', '{{%admin_storage_file}}', ['name_new_compound']);

        // admin_storage_filter_chain
        $this->createIndex('index_filter_id', '{{%admin_storage_filter_chain}}', ['filter_id']);
        $this->createIndex('index_effect_id', '{{%admin_storage_filter_chain}}', ['effect_id']);

        // admin_storage_image
        $this->createIndex('index_image_filter_id', '{{%admin_storage_image}}', ['filter_id']);
        $this->createIndex('index_file_id_filter_id', '{{%admin_storage_image}}', ['file_id', 'filter_id']);

        // admin_tag_relation
        $this->createIndex('index_tag_id', '{{%admin_tag_relation}}', ['tag_id']);
        $this->createIndex('index_table_name', '{{%admin_tag_relation}}', ['table_name']);
        $this->createIndex('index_pk_id', '{{%admin_tag_relation}}', ['pk_id']);
        $this->createIndex('index_table_name_pk_id', '{{%admin_tag_relation}}', ['table_name', 'pk_id']);

        // admin_user
        $this->createIndex('index_email', '{{%admin_user}}', ['email']);
        $this->createIndex('index_admin_user_auth_token', '{{%admin_user}}', ['auth_token']);
        $this->createIndex('index_is_deleted_auth_token', '{{%admin_user}}', ['is_deleted', 'auth_token']);
        $this->createIndex('index_is_deleted_id', '{{%admin_user}}', ['is_deleted', 'id']);

        // admin_user_group
        $this->createindex('index_admin_user_group_user_id', '{{%admin_user_group}}', ['user_id']);
        $this->createIndex('index_admin_user_group_group_id', '{{%admin_user_group}}', ['group_id']);
        $this->createIndex('index_user_id_group_id', '{{%admin_user_group}}', ['user_id', 'group_id']);

        // admin_user_login
        $this->createIndex('index_admin_user_login_user_id', '{{%admin_user_login}}', ['user_id']);
        $this->createIndex('index_ip', '{{%admin_user_login}}', ['ip']);
        $this->createIndex('index_admin_user_login_auth_token', '{{%admin_user_login}}', ['auth_token']);
        $this->createIndex('index_is_destroyed', '{{%admin_user_login}}', ['is_destroyed']);
        $this->createIndex('index_user_id_timestamp_create', '{{%admin_user_login}}', ['user_id', 'timestamp_create']);

        // admin_user_online
        $this->createIndex('index_admin_user_online_user_id', '{{%admin_user_online}}', ['user_id']);
    }


    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180723_120432_indexes cannot be reverted.\n";

        return false;
    }
}
