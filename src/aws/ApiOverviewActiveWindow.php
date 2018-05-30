<?php

namespace luya\admin\aws;

use Yii;
use luya\admin\ngrest\base\ActiveWindow;
use luya\admin\Module;
use luya\helpers\Inflector;

/**
 * Api Overview Active Window.
 *
 * File has been created with `aw/create` command.
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.1.0
 */
class ApiOverviewActiveWindow extends ActiveWindow
{
    /**
     * @var string The name of the module where the ActiveWindow is located in order to finde the view path.
     */
    public $module = '@admin';

    /**
     * Default label if not set in the ngrest model.
     *
     * @return string The name of of the ActiveWindow. This is displayed in the CRUD list.
     */
    public function defaultLabel()
    {
        return Module::t('aw_apioverview_label');
    }

    /**
     * Default icon if not set in the ngrest model.
     *
     * @var string The icon name from goolges material icon set (https://material.io/icons/)
     */
    public function defaultIcon()
    {
        return 'device_hub';
    }

    /**
     * The default action which is going to be requested when clicking the ActiveWindow.
     *
     * @return string The response string, render and displayed trough the angular ajax request.
     */
    public function index()
    {
        return $this->render('index', [
            'model' => $this->model,
            'endpoints' => $this->getAvailableApiEndpoints(),
            'groupsCount' => $this->model->getGroups()->count(),
        ]);
    }
    
    protected function getAvailableApiEndpoints()
    {
        $data = [];
        
        $fromPermission = Yii::$app->auth->getPermissionTableDistinct($this->model->id);
        
        // get APIs from permission system
        foreach ($fromPermission as $permission) {
            if (!empty($permission['api'])) {
                $data[$permission['api']] = [
                    'api' => $permission['api'],
                    'crud_create' => $permission['crud_create'],
                    'crud_update' => $permission['crud_update'],
                    'crud_delete' => $permission['crud_delete'],
                    'permission' => true,
                    'actions' => [],
                ];
            }
        }
        
        // get missing apis from controller map
        
        $maps = Yii::$app->getModule('admin')->controllerMap;
        
        foreach ($maps as $key => $value) {
            if (!isset($data[$key])) {
                $ctrl = Yii::createObject($value['class'], [$key, $this]);
                
                $data[$key] = [
                    'api' => $key,
                    'crud_create' => false,
                    'crud_update' => false,
                    'crud_delete' => false,
                    'permission' => false,
                    'actions' => $this->getActions($ctrl),
                ];
            }
        }
        
        // sort and return
        ksort($data);
        return $data;
    }
    
    /**
     * Returns all available actions of the specified controller.
     * @param \yii\base\Controller $controller the controller instance
     * @return array all available action IDs.
     */
    public function getActions($controller)
    {
        $actions = array_keys($controller->actions());
        $class = new \ReflectionClass($controller);
        foreach ($class->getMethods() as $method) {
            $name = $method->getName();
            if ($name !== 'actions' && $method->isPublic() && !$method->isStatic() && strncmp($name, 'action', 6) === 0) {
                $actions[] = Inflector::camel2id(substr($name, 6), '-', true);
            }
        }
        sort($actions);
        return array_unique($actions);
    }
    
    public function callbackReplaceToken()
    {
        $randomToken = Yii::$app->security->hashData(Yii::$app->security->generateRandomString(), $this->model->password_salt);
        
        $this->model->updateAttributes([
            'auth_token' => $randomToken,
        ]);
    }
}
