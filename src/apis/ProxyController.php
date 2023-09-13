<?php

namespace luya\admin\apis;

use luya\admin\models\ProxyBuild;
use luya\admin\models\ProxyMachine;
use luya\admin\models\StorageFile;
use luya\admin\Module;
use luya\helpers\Url;
use luya\rest\Controller;
use Yii;
use yii\db\Connection;
use yii\db\Query;
use yii\di\Instance;
use yii\helpers\Json;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

/**
 * Proxy API.
 *
 * How the data is prepared:
 *
 * 1. Foreach all tables
 * 2. Ignore the $ingoreTables list
 * 3. Table request estimated data write to $config
 * 4. Generate Build.
 * 5. Send build identifier to the client.
 *
 * @property Module $module
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.0
 */
class ProxyController extends Controller
{
    /**
     * @var Connection
     * @since 2.0.0
     */
    protected $db;

    /**
     * @var array A list of tables which will be ignored and can not be synced with the proxy command.
     */
    protected $ignoreTables = [
        'migration', 'admin_proxy_build', 'admin_proxy_machine',
    ];

    /**
     * {@inheritDoc}
     */
    public function init()
    {
        parent::init();
        $this->db = Instance::ensure($this->module->proxyDbConnection, Connection::class);
    }

    /**
     * Gathers basic informations about the build.
     *
     * @param string $identifier
     * @param string $token
     * @throws ForbiddenHttpException
     * @return array
     */
    public function actionIndex($identifier, $token)
    {
        $machine = ProxyMachine::findOne(['identifier' => $identifier, 'is_deleted' => false]);

        if (!$machine) {
            throw new ForbiddenHttpException("Unable to acccess the proxy api.");
        }

        if (sha1($machine->access_token) !== $token) {
            throw new ForbiddenHttpException("Unable to acccess the proxy api due to invalid token.");
        }

        $rowsPerRequest = $this->module->proxyRowsPerRequest;

        $config = [
            'rowsPerRequest' => $rowsPerRequest,
            'tables' => [],
            'storageFilesCount' => StorageFile::find()->count(),
        ];

        foreach ($this->db->schema->tableNames as $table) {
            if (in_array($table, $this->ignoreTables)) {
                continue;
            }

            $schema = $this->db->getTableSchema($table);
            $rows = (new Query())->from($table)->count('*', $this->db);
            $config['tables'][$table] = [
                'pks' => $schema->primaryKey,
                'name' => $table,
                'rows' => $rows,
                'fields' => $schema->columnNames,
                'offset_total' => ceil($rows / $rowsPerRequest),
            ];
        }

        $buildToken = Yii::$app->security->generateRandomString(16);

        $build = new ProxyBuild();
        $build->detachBehavior('LogBehavior');
        $build->attributes = [
            'machine_id' => $machine->id,
            'timestamp' => time(),
            'build_token' => sha1($buildToken),
            'config' => Json::encode($config),
            'is_complet' => 0,
            'expiration_time' => time() + $this->module->proxyExpirationTime
        ];

        if ($build->save()) {
            return [
                'providerUrl' => Url::base(true) . '/admin/api-admin-proxy/data-provider',
                'requestCloseUrl' => Url::base(true) . '/admin/api-admin-proxy/close',
                'fileProviderUrl' => Url::base(true) . '/admin/api-admin-proxy/file-provider',
                'imageProviderUrl' => Url::base(true) . '/admin/api-admin-proxy/image-provider',
                'buildToken' => $buildToken,
                'config' => $config,
            ];
        }

        return $build->getErrors();
    }

    /**
     * Make sure the machine and token are valid.
     *
     * @param string $machine
     * @param string $buildToken
     * @throws ForbiddenHttpException
     * @return \luya\admin\models\ProxyBuild
     */
    private function ensureBuild($machine, $buildToken)
    {
        $build = ProxyBuild::findOne(['build_token' => $buildToken, 'is_complet' => 0]);

        if (!$build) {
            throw new ForbiddenHttpException("Unable to find a ProxyBuild for the provided token.");
        }

        if (time() > $build->expiration_time) {
            throw new ForbiddenHttpException("The expiration time ".date("d.m.Y H:i:s", $build->expiration_time)." has exceeded.");
        }

        if (!$build->proxyMachine || $build->proxyMachine->identifier !== $machine) {
            throw new ForbiddenHttpException("Invalid machine identifier for current build.");
        }

        return $build;
    }

    /**
     * Return sql table data.
     *
     * @param string $machine
     * @param string $buildToken
     * @param string $table
     * @param integer $offset
     * @return array An array with all data for the given $table
     */
    public function actionDataProvider($machine, $buildToken, $table, $offset)
    {
        $build = $this->ensureBuild($machine, $buildToken);

        $config = $build->getTableConfig($table);

        $offsetNummeric = $offset * $build->rowsPerRequest;

        $query =  (new Query())
            ->select($config['fields'])
            ->from($config['name'])
            ->offset($offsetNummeric)
            ->limit($build->rowsPerRequest);

        if (!empty($config['pks']) && is_array($config['pks'])) {
            $orders = [];
            foreach ($config['pks'] as $pk) {
                $orders[$pk] = SORT_ASC;
            }
            $query->orderBy($orders);
        }

        return $query->all($this->db);
    }

    /**
     * Return file storage data.
     *
     * @param string $machine
     * @param string $buildToken
     * @param integer $fileId
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function actionFileProvider($machine, $buildToken, $fileId)
    {
        $build = $this->ensureBuild($machine, $buildToken);

        if ($build) {
            if (!is_numeric($fileId)) {
                throw new ForbiddenHttpException("Invalid file id input.");
            }

            $file = Yii::$app->storage->getFile($fileId);
            /* @var $file \luya\admin\file\Item */
            if ($file && $file->fileExists) {
                return Yii::$app->response->sendContentAsFile($file->getContent(), $file->systemFileName, null, ['mimeType' => $file->mimeType])->send();
            }

            throw new NotFoundHttpException("The requested file '".$fileId."' does not exist in the storage folder.");
        }
    }

    /**
     * Return image storage data.
     *
     * @param string $machine
     * @param string $buildToken
     * @param integer $imageId
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function actionImageProvider($machine, $buildToken, $imageId)
    {
        $build = $this->ensureBuild($machine, $buildToken);

        if ($build) {
            if (!is_numeric($imageId)) {
                throw new ForbiddenHttpException("Invalid image id input.");
            }

            $image = Yii::$app->storage->getImage($imageId);
            /* @var $image \luya\admin\image\Item */
            if ($image && $image->fileExists) {
                return Yii::$app->response->sendContentAsFile($image->getContent(), $image->systemFileName)->send();
            }

            throw new NotFoundHttpException("The requested image '".$imageId."' does not exist in the storage folder.");
        }
    }

    /**
     * Close the current build.
     *
     * @param string $buildToken
     * @throws ForbiddenHttpException
     */
    public function actionClose($buildToken)
    {
        $build = ProxyBuild::findOne(['build_token' => $buildToken, 'is_complet' => 0]);

        if (!$build) {
            throw new ForbiddenHttpException("Unable to find build from token.");
        }

        $build->updateAttributes(['is_complet' => 1]);
    }
}
