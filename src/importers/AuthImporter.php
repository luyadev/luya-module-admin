<?php

namespace luya\admin\importers;

use luya\console\Importer;
use Yii;

/**
 * Import Auth Apis and Routes.
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.0
 */
class AuthImporter extends Importer
{
    public function run()
    {
        $modules = Yii::$app->getModules();
        $ids = [];
        foreach ($modules as $id => $moduleObject) {
            $object = Yii::$app->getModule($id);
            if (method_exists($object, 'getAuthApis')) {
                foreach ($object->getAuthApis() as $item) {
                    $ids[] = Yii::$app->auth->addApi($object->id, $item['api'], $item['alias'], $item['pool'] ?? null);
                }
            }

            if (method_exists($object, 'getAuthRoutes')) {
                foreach ($object->getAuthRoutes() as $item) {
                    $ids[] = Yii::$app->auth->addRoute($object->id, $item['route'], $item['alias']);
                }
            }
        }

        $toClean = Yii::$app->auth->prepareCleanup($ids);
        if ((is_countable($toClean) ? count($toClean) : 0) > 0) {
            foreach ($toClean as $rule) {
                $this->addLog('Deleted old menu auth rule "'.$rule['alias_name'].'" in module '.$rule['module_name'].'.');
            }

            Yii::$app->auth->executeCleanup($toClean);
        }
    }
}
