<?php

namespace luya\admin\aws;

use luya\admin\Module;
use luya\admin\ngrest\base\ActiveWindow;
use luya\helpers\ArrayHelper;
use Yii;
use yii\db\Query;

/**
 * Active Window to set permissions for a specific Group, used in groups ngrest model.
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.0
 */
class GroupAuthActiveWindow extends ActiveWindow
{
    /**
     * @var string The name of the module where the ActiveWindow is located in order to find the view path.
     */
    public $module = '@admin';

    /**
     * The default action which is going to be requested when clicking the ActiveWindow.
     *
     * @return string The response string, render and displayed trough the angular ajax request.
     */
    public function index()
    {
        return $this->render('index');
    }

    /**
     * @inheritdoc
     */
    public function defaultLabel()
    {
        return Module::t('model_group_btn_aws_groupauth');
    }

    /**
     * @inheritdoc
     */
    public function defaultIcon()
    {
        return 'verified_user';
    }

    /**
     *
     * @return boolean[]
     */
    public function callbackSaveRights()
    {
        $rights = Yii::$app->request->post('data', []);

        $safeCopy = [];

        foreach ($rights as $authId => $options) {
            if (!empty($options) && isset($options['base']) && $options['base'] == 1) {
                $safeCopy[$authId] = $options;
            }
        }

        Yii::$app->db->createCommand()->delete('{{%admin_group_auth}}', ['group_id' => $this->getItemId()])->execute();

        foreach ($safeCopy as $authId => $options) {
            Yii::$app->db->createCommand()->insert('{{%admin_group_auth}}', [
                'group_id' => $this->getItemId(),
                'auth_id' => $authId,
                'crud_create' => (isset($options['create']) && $options['create'] == 1) ? 1 : 0,
                'crud_update' => (isset($options['update']) && $options['update'] == 1) ? 1 : 0,
                'crud_delete' => (isset($options['delete']) && $options['delete'] == 1) ? 1 : 0,
            ])->execute();
        }

        return ['success' => true];
    }

    /**
     *
     * @return number[][][]|string[]|array[]
     */
    public function callbackGetRights()
    {
        return [
            'rights' => $this->getRightsData(),
            'auths' => $this->getAuthData(),
        ];
    }

    /**
     *
     * @return string|array
     */
    private function getAuthData()
    {
        $data = (new Query())->select(['*'])->from('{{%admin_auth}}')->orderBy(['module_name' => SORT_ASC, 'alias_name' => SORT_ASC])->all();

        array_walk($data, function (&$item, $key) {
            try {
                $item['alias_name'] = Yii::t($item['module_name'], $item['alias_name'], [], Yii::$app->language);
            } catch (\Exception $e) {
            }
        });

        return ArrayHelper::index($data, null, 'module_name');
    }

    /**
     *
     * @return number[][]
     */
    private function getRightsData()
    {
        $query = (new \yii\db\Query())->select('*')->from('{{%admin_group_auth}}')->where(['group_id' => $this->getItemId()])->all();

        $subs = [];

        foreach ($query as $item) {
            $subs[$item['auth_id']] = [
                'base' => 1,
                'create' => (int) $item['crud_create'],
                'update' => (int) $item['crud_update'],
                'delete' => (int) $item['crud_delete'],
            ];
        }

        return $subs;
    }
}
