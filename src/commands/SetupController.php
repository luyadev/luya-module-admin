<?php

namespace luya\admin\commands;

use luya\admin\models\Config;
use luya\admin\models\Group;
use luya\admin\models\User;
use Yii;
use yii\console\Exception;
use yii\db\Query;
use yii\helpers\VarDumper;
use yii\imagine\Image;

/**
 * Setup the Administration Interface.
 *
 * You can also use the parameters to run the setup command for example.
 *
 * ```php
 * ./vendor/bin/luya admin/setup --email=foo@bar.com --password=test --firstname=John --lastname=Doe --interactive=0
 * ```
 *
 * This will perform the Setup task silent and does not prompt any questions.
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.0
 */
class SetupController extends \luya\console\Command
{
    /**
     * @var string The email of the user to create or change.
     */
    public $email;

    /**
     * @var string The blank password of the user to create or change.
     */
    public $password;

    /**
     * @var string The first name of the user to create.
     */
    public $firstname;

    /**
     * @var string The last name of the user to create.
     */
    public $lastname;

    /**
     * @var string Whether the setup is interactive or not.
     */
    public $interactive = true;

    /**
     * @var string The name of the default language, e.g. English
     */
    public $langName;

    /**
     * @var string The short code of the language, e.g. en
     */
    public $langShortCode;

    /**
     * @inheritdoc
     */
    public function options($actionID)
    {
        return ['email', 'password', 'firstname', 'lastname', 'interactive'];
    }

    /**
     * Setup the administration area.
     *
     * This action of setup will add a new user, group, language, permissions, default homepage and container.
     *
     * @return boolean
     */
    public function actionIndex()
    {
        try {
            Image::getImagine();
        } catch (\Exception $e) {
            return $this->outputError('Setup error - Unable to find image library: ' . $e->getMessage());
        }

        if (!Config::has(Config::CONFIG_LAST_IMPORT_TIMESTAMP)) {
            return $this->outputError("You have to run the 'import' process first. Run in terminal: ./vendor/bin/luya import");
        }

        if (Config::has(Config::CONFIG_SETUP_COMMAND_TIMESTAMP)) {
            return $this->outputError('The setup process already have been executed at '.date('d.m.Y H:i', Config::get(Config::CONFIG_SETUP_COMMAND_TIMESTAMP)).'. If you like to reinstall your application, drop all tables from your database, run the migrate and import command and then re-run the setup command.');
        }

        if (empty($this->email)) {
            $this->email = $this->prompt('User Email:', ['required' => true]);
        }

        if (empty($this->password)) {
            $this->password = $this->prompt('User Password:', ['required' => true]);
        }

        if (empty($this->firstname)) {
            $this->firstname = $this->prompt('First Name:', ['required' => true]);
        }

        if (empty($this->lastname)) {
            $this->lastname = $this->prompt('Last Name:', ['required' => true]);
        }

        if (empty($this->langName)) {
            $this->langName = $this->prompt('Default Language:', ['required' => true, 'default' => 'English']);
        }

        if (empty($this->langShortCode)) {
            $this->langShortCode = $this->prompt('Short Code of the Default Language:', ['required' => true, 'default' => 'en', 'validator' => function ($input, &$error) {
                if (strlen($input) !== 2) {
                    $error = 'The Short Code must be 2 chars length only. Examples: de, en, fr, ru';
                    return false;
                }
                return true;
            }]);
        }

        if ($this->interactive) {
            $this->outputInfo('User Email: '. $this->email);
            $this->outputInfo('First Name: '. $this->firstname);
            $this->outputInfo('Last Name: '. $this->lastname);
            $this->outputInfo('Default Language: '. $this->langName);

            if ($this->confirm("Confirm your login details in order to proceed with the setup. Are those informations correct?") !== true) {
                return $this->outputError('Aborted by user.');
            }
        }

        $salt = Yii::$app->security->generateRandomString();
        $pw = Yii::$app->security->generatePasswordHash($this->password.$salt);

        $this->insert('{{%admin_user}}', [
            'title' => 1,
            'firstname' => $this->firstname,
            'lastname' => $this->lastname,
            'email' => $this->email,
            'password' => $pw,
            'password_salt' => $salt,
            'is_deleted' => false,
        ]);

        $this->insert('{{%admin_group}}', [
            'name' => 'Administrator',
            'text' => 'Administrator accounts have full access to all areas and can create, update and delete all data records.',
        ]);

        $this->insert('{{%admin_user_group}}', [
            'user_id' => 1,
            'group_id' => 1,
        ]);

        // get the api-admin-user and api-admin-group auth rights
        $data = (new Query())->select(['id'])->from('{{%admin_auth}}')->all();

        foreach ($data as $item) {
            $this->insert('{{%admin_group_auth}}', [
                'group_id' => 1,
                'auth_id' => $item['id'],
                'crud_create' => 1,
                'crud_update' => 1,
                'crud_delete' => 1,
            ]);
        }

        $this->insert('{{%admin_lang}}', [
            'name' => $this->langName,
            'short_code' => $this->langShortCode,
            'is_default' => true,
        ]);

        if (Yii::$app->hasModule('cms')) {
            // insert default page
            $this->insert("cms_nav_container", ['id' => 1, 'name' => 'Default Container', 'alias' => 'default', 'is_deleted' => false]);
            $this->insert("cms_nav", ['nav_container_id' => 1, 'parent_nav_id' => 0, 'sort_index' => 0, 'is_deleted' => false, 'is_hidden' => false, 'is_offline' => false, 'is_home' => true, 'is_draft' => false]);
            $this->insert("cms_nav_item", ['nav_id' => 1, 'lang_id' => 1, 'nav_item_type' => 1, 'nav_item_type_id' => 1, 'create_user_id' => 1, 'update_user_id' => 1, 'timestamp_create' => time(), 'title' => 'Homepage', 'alias' => 'homepage']);
            $this->insert('cms_nav_item_page', ['layout_id' => 1, 'create_user_id' => 1, 'timestamp_create' => time(), 'version_alias' => 'Initial', 'nav_item_id' => 1]);
        }

        Config::set(Config::CONFIG_SETUP_COMMAND_TIMESTAMP, time());

        return $this->outputSuccess("Setup is finished. You can now login into the administration area with the email '{$this->email}'.");
    }

    /**
     * Create a new user and append them to an existing group.
     * @return bool
     * @throws Exception
     */
    public function actionUser()
    {
        while (true) {
            $email = $this->prompt('User Email:');
            if (!empty(User::findByEmail($email))) {
                $this->outputError('The provided email already exists in the system.');
            } else {
                break;
            }
        }

        $titleArray = ['Mr' => 1, 'Mrs' => 2];
        $title = $this->select('Title:', $titleArray);

        $firstname = $this->prompt('First Name:');
        $lastname = $this->prompt('Last Name:');
        $password = $this->prompt('User Password:');

        if ($this->confirm("Are you sure to create the user '$email'?") !== true) {
            return $this->outputError('Abort user creation process.');
        }

        $user = new User();
        $user->email = $email;
        $user->password_salt = Yii::$app->getSecurity()->generateRandomString();
        $user->password = Yii::$app->getSecurity()->generatePasswordHash($password.$user->password_salt);
        $user->firstname = $firstname;
        $user->lastname = $lastname;
        $user->title = $titleArray[$title];
        if (!$user->save()) {
            return $this->outputError('User validation error: ' . VarDumper::dumpAsString($user->getErrors()));
        }

        $groupSelect = [];

        foreach (Group::find()->all() as $entry) {
            $groupSelect[$entry->id] = $entry->name.' ('.$entry->text.')';
            $this->output($entry->id.' - '.$groupSelect[$entry->id]);
        }
        $groupId = $this->select('Select group the user should belong to:', $groupSelect);

        $this->insert('{{%admin_user_group}}', [
            'user_id' => $user->id,
            'group_id' => $groupId,
        ]);

        return $this->outputSuccess("The user '$email' has been created.");
    }

    /**
     * Change the password of an admin user.
     *
     * @return bool
     * @since 2.0.0
     */
    public function actionResetPassword()
    {
        $email = null;
        /** @var User $user */
        $user = null;

        while (empty($user)) {
            $email = $this->email ?: $this->prompt('User Email:');
            $user = User::findByEmail($email);
            if (empty($user)) {
                $this->outputError('The provided email was not found in the system.');
            }
        }

        $password = $this->password ?: $this->prompt('User Password:');

        if ($this->confirm("Are you sure to change the password of user '$email'?") !== true) {
            return $this->outputError('Abort password change process.');
        }

        $user->password_salt = Yii::$app->getSecurity()->generateRandomString();
        $user->password = Yii::$app->getSecurity()->generatePasswordHash($password.$user->password_salt);
        if ($user->save(true, ['password', 'password_salt'])) {
            return $this->outputSuccess("The password for user '$email' has been changed.");
        }

        return $this->outputError("The password could not be changed.\n" . implode("\n", $user->firstErrors));
    }

    /**
     * Helper to insert data in database table.
     *
     * @param string $table The database table
     * @param array $fields The array with insert fields
     * @return int
     */
    private function insert($table, array $fields)
    {
        return Yii::$app->db->createCommand()->insert($table, $fields)->execute();
    }
}
