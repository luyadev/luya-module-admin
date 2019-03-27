<?php

namespace luya\admin\components;

use Yii;
use luya\Exception;
use luya\admin\models\Auth as AuthModel;
use yii\db\Query;
use yii\helpers\ArrayHelper;

/**
 * Auth components gives informations about permissions, who can do what.
 *
 * In order to understand the permission weights read the [[app-admin-module-permission.md]] section.
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.0
 */
class Auth extends \yii\base\Component
{
    /**
     * @var integer Can View/list records
     * @since 2.0.0
     */
    const CAN_VIEW = 0;

    /**
     * @var integer Can create new records
     */
    const CAN_CREATE = 1;

    /**
     * @var integer Can update records
     */
    const CAN_UPDATE = 2;

    /**
     * @var integer Can delete records.
     */
    const CAN_DELETE = 3;
    
    private $_permissionTable;
    
    /**
     * Get all permissions entries for the given User.
     *
     * @param integer $userId The user id to retrieve the data for.
     * @return array
     */
    public function getPermissionTable($userId)
    {
        if ($this->_permissionTable === null) {
            $this->_permissionTable = (new Query())
                ->select(['*'])
                ->from('{{%admin_user_group}}')
                ->innerJoin('{{%admin_group_auth}}', '{{%admin_user_group}}.group_id={{%admin_group_auth}}.group_id')
                ->innerJoin('{{%admin_auth}}', '{{%admin_group_auth}}.auth_id = {{%admin_auth}}.id')
                ->where(['{{%admin_user_group}}.user_id' => $userId])
                ->all();
        }
        
        return $this->_permissionTable;
    }
    
    /**
     * Get the permission table for a user without doublicated entries.
     *
     * As its possible to have multiple groups with the same permissions.
     *
     * @param integer $userId
     * @return array
     */
    public function getPermissionTableDistinct($userId)
    {
        return ArrayHelper::index($this->getPermissionTable($userId), 'id');
    }
    
    /**
     * Get the data for a given api and user.
     *
     * @param integer $userId The user id the find the data from.
     * @param string $apiEndpoint The api endpoint to find from the permission system.
     * @return array
     */
    public function getApiTable($userId, $apiEndpoint)
    {
        $data = [];
        foreach ($this->getPermissionTable($userId) as $item) {
            if ($item['api'] == $apiEndpoint && $item['user_id'] == $userId) {
                $data[] = $item;
            }
        }
        return $data;
    }
    
    /**
     * Get the data for a given route and user.
     *
     * @param integer $userId The user id the find the data from.
     * @param string $route The route to find from the permission system.
     * @return array
     */
    public function getRouteTable($userId, $route)
    {
        $data = [];
        foreach ($this->getPermissionTable($userId) as $item) {
            if ($item['route'] == $route && $item['user_id'] == $userId) {
                $data[] = $item;
            }
        }
        return $data;
    }
    
    /**
     * Calculate the weight from whether the user can create, udpate and/or delete.
     *
     * @param integer $create Whether the user can create new records
     * @param integer $update Whether the user can update records.
     * @param integer $delete Whether the user can delete records.
     * @return number The calculated weight of the permissions based on the input.
     */
    public function permissionWeight($create, $update, $delete)
    {
        $create = $create ? 1 : 0;
        $update = $update ? 3 : 0;
        $delete = $delete ? 5 : 0;

        return ($create + $update + $delete);
    }

    /**
     * Verify a permission type against its calculated `weight`.
     *
     * In order to calculate the permissions weight see {{\luya\admin\components\Auth::permissionWeight}}.
     *
     * @param string $type The type of permission (1,2,3 see constants)
     * @param integer $permissionWeight A weight of the permssions which is value between 1 - 9, see [[app-admin-module-permission.md]].
     * @return boolean
     */
    public function permissionVerify($type, $permissionWeight)
    {
        $numbers = [];
        
        switch ($type) {
            case self::CAN_CREATE:
                $numbers = [1, 4, 6, 9];
                break;
            case self::CAN_UPDATE:
                $numbers = [3, 4, 8, 9];
                break;
            case self::CAN_DELETE:
                $numbers = [5, 6, 8, 9];
                break;
        }

        return in_array($permissionWeight, $numbers);
    }

    /**
     * See if a User have rights to access this api.
     *
     * @param integer $userId
     * @param string $apiEndpoint As defined in the Module.php like (api-admin-user) which is a unique identifiere
     * @param integer|string $typeVerification The CONST number provided from CAN_*
     * @return bool
     */
    public function matchApi($userId, $apiEndpoint, $typeVerification = false)
    {
        $groups = $this->getApiTable($userId, $apiEndpoint);

        if ($typeVerification === false || $typeVerification === self::CAN_VIEW) {
            return (count($groups) > 0) ? true : false;
        }

        foreach ($groups as $row) {
            if ($this->permissionVerify($typeVerification, $this->permissionWeight($row['crud_create'], $row['crud_update'], $row['crud_delete']))) {
                return true;
            }
        }

        return false;
    }
    
    /**
     * See if the user has permitted the provided route.
     *
     * @param integer $userId The user id from admin users
     * @param string $route The route to test.
     * @return boolean
     */
    public function matchRoute($userId, $route)
    {
        $groups = $this->getRouteTable($userId, $route);
        
        if (is_array($groups) && count($groups) > 0) {
            return true;
        }

        return false;
    }

    /**
     * Add a new route to the permission system (admin_auth)
     *
     * @param string $moduleName The name of the module where the route is located.
     * @param string $route The route which is an identifier.
     * @param string $name A readable name for the route to display in the permissions system.
     * @throws \luya\Exception
     * @return integer
     */
    public function addRoute($moduleName, $route, $name)
    {
        $model = AuthModel::find()->where(['route' => $route])->one();

        if (!$model) {
            $model = new AuthModel();
        }

        $model->alias_name = $name;
        $model->module_name = $moduleName;
        $model->route = $route;
        $model->save();

        return $model->id;
    }

    /**
     * Add a new api route to the permission system (admin_auth)
     *
     * @param string $moduleName The name of the module where the route is located.
     * @param string $apiEndpoint An API endpoint name like `admin-user-group` which is an identifier.
     * @param string $name A readable name for the api to display in the permission system.
     * @param string $pool
     * @return integer
     */
    public function addApi($moduleName, $apiEndpoint, $name, $pool = null)
    {
        $where = ['api' => $apiEndpoint];
        if (!empty($pool)) {
            $where['pool'] = $pool;
        }

        $model = AuthModel::find()->where($where)->one();

        if (!$model) {
            $model = new AuthModel();
        }

        $model->alias_name = $name;
        $model->module_name = $moduleName;
        $model->pool = $pool;
        $model->api = $apiEndpoint;
        $model->is_crud = 1;
        $model->save();

        return $model->id;
    }

    /**
     * Returns the current available auth rules inside the admin_auth table splied into routes and apis.
     *
     * @return array
     */
    public function getDatabaseAuths()
    {
        // define response structure of array
        $data = [
            'routes' => [],
            'apis' => [],
        ];
        // get all auth data
        foreach ((new Query())->select('*')->from('{{%admin_auth}}')->all() as $item) {
            // allocate if its an api or route. More differences?
            if (empty($item['api'])) {
                $data['routes'][] = $item;
            } else {
                $data['apis'][] = $item;
            }
        }

        return $data;
    }

    /**
     * The method returns all rows which are not provided in $array. If an api/route is in the $data array its a valid rule and will not be
     * prepared to find for deletion. Negativ array behavior.
     *
     * ```php
     * $data = [
     *     'apis' => ['api-admin-test', 'api-admin-foo'],
     *     'routes' => ['route-to-sth', 'foo-bar-bar'],
     * ];
     * ```
     *
     * The above provided data are valid rules.
     *
     * @param array $data array with key apis and routes
     * @return array
     */
    public function prepareCleanup(array $ids)
    {
        return AuthModel::find()->where(['not in', 'id', $ids])->asArray()->all();
    }

    /**
     * Execute the data to delete based on an array containing a key 'id' with the corresponding value from the Database.
     *
     * @param array $data
     * @return bool
     */
    public function executeCleanup(array $data)
    {
        foreach ($data as $rule) {
            Yii::$app->db->createCommand()->delete('{{%admin_auth}}', 'id=:id', ['id' => $rule['id']])->execute();
            Yii::$app->db->createCommand()->delete('{{%admin_group_auth}}', 'auth_id=:id', ['id' => $rule['id']])->execute();
        }

        return true;
    }
}
