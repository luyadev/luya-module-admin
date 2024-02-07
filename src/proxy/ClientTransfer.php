<?php

namespace luya\admin\proxy;

use Curl\Curl;
use Exception;
use luya\admin\file\Query;
use luya\admin\image\Item;
use luya\helpers\FileHelper;
use luya\traits\CacheableTrait;
use Yii;
use yii\base\BaseObject;
use yii\base\InvalidConfigException;

/**
 * Client Transfer Process
 *
 * For `admin/proxy` usage see {{luya\admin\commands\ProxyController}}
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.0
 */
class ClientTransfer extends BaseObject
{
    use CacheableTrait;
    public const ONLY_STORAGE = 'storage';

    public const ONLY_DB = 'db';

    public $only;

    /**
     * {@inheritDoc}
     */
    public function init()
    {
        parent::init();

        if ($this->build === null) {
            throw new InvalidConfigException("The build property can not be empty.");
        }
    }

    /**
     * Start DB Sync
     *
     * @since 4.0.0
     */
    protected function startDb()
    {
        if ($this->build->db->schema instanceof \yii\db\mysql\Schema) {
            $this->build->command->outputInfo('Using local database ' . $this->build->db->createCommand('SELECT DATABASE()')->queryScalar());
        }

        foreach ($this->build->getTables() as $name => $table) {
            $table->syncData();
        }
    }

    /**
     * Start Storage Files Sync
     *
     * @since 4.0.0
     */
    protected function startFiles()
    {
        $fileCount = 0;
        // sync files
        foreach ((new Query())->where(['is_deleted' => false])->all() as $file) {
            /** @var \luya\admin\file\Item $file */
            if (!$file->fileExists) {
                $curl = new Curl();
                $curl->setOpt(CURLOPT_RETURNTRANSFER, true);
                $curl->get($this->build->fileProviderUrl, [
                    'buildToken' => $this->build->buildToken,
                    'machine' => $this->build->machineIdentifier,
                    'fileId' => $file->id,
                ]);

                if (!$curl->error) {
                    $md5 = $this->storageUpload($file->systemFileName, $curl->response);
                    if ($md5) {
                        if ($md5 == $file->getFileHash()) {
                            $fileCount++;
                            $this->build->command->outputInfo('[+] File ' . $file->name . ' ('.$file->systemFileName.') downloaded.');
                        } else {
                            $this->build->command->outputError('[!] Downloaded file checksum "'.$md5.'" does not match server checksum "'.$file->getFileHash().'" for file ' . $file->systemFileName.'.');
                        }
                    } else {
                        $this->build->command->outputError('[!] Unable to temporary store the file ' . $file->systemFileName.'.');
                    }
                } else {
                    $this->build->command->outputError('[!] File ' . $file->systemFileName. ' download request error: "'. $curl->error_message.'".');
                }

                $curl->close();
                unset($curl);
                gc_collect_cycles();
            }
        }

        $this->build->command->outputInfo("[=] {$fileCount} Files downloaded.");
    }

    /**
     * Start Storage Images Sync
     *
     * @since 4.0.0
     */
    protected function startImages()
    {
        $imageCount = 0;
        // sync images
        foreach ((new \luya\admin\image\Query())->all() as $image) {
            try {
                /** @var Item $image */
                if (!$image->fileExists) {
                    $curl = new Curl();
                    $curl->setOpt(CURLOPT_RETURNTRANSFER, true);
                    $curl->get($this->build->imageProviderUrl, [
                        'buildToken' => $this->build->buildToken,
                        'machine' => $this->build->machineIdentifier,
                        'imageId' => $image->id,
                    ]);

                    if (!$curl->error) {
                        if ($this->storageUpload($image->systemFileName, $curl->response)) {
                            $imageCount++;
                            $this->build->command->outputInfo('[+] Image ' . $image->source.' downloaded.');
                        }
                    }

                    $curl->close();
                    unset($curl);
                    gc_collect_cycles();
                }
            } catch (Exception $e) {
                $this->build->command->outputError('[!] Unable to download image due to error: ' . $e->getMessage());
            }
        }

        $this->build->command->outputInfo("[=] {$imageCount} Images downloaded.");
    }

    /**
     * @var ClientBuild
     */
    public $build;

    public function start()
    {
        $this->flushHasCache();

        if (empty($this->only) || $this->only == self::ONLY_DB) {
            $this->startDb();
        }

        if (empty($this->only) || $this->only == self::ONLY_STORAGE) {
            $this->startFiles();
            $this->startImages();
        }

        // close the build
        $curl = new Curl();
        $curl->get($this->build->requestCloseUrl, ['buildToken' => $this->build->buildToken]);

        return true;
    }

    /**
     * Upload file to storage.
     *
     * Upload the given filename with its content into the websites storage system and return the md5 checksum of the uploaded file.
     *
     * @param string $fileName
     * @param string $content
     * @return string|false Either returns the md5 hash of the uploaded file or false if something went wrong
     * @since 3.6.0
     */
    public function storageUpload($fileName, $content)
    {
        try {
            $fromTempFile = @tempnam(sys_get_temp_dir(), 'clientTransferUpload');
            FileHelper::writeFile($fromTempFile, $content);

            $result = Yii::$app->storage->fileSystemSaveFile($fromTempFile, $fileName);

            if (!$result) {
                return false;
            }

            $md5 = FileHelper::md5sum($fromTempFile);

            FileHelper::unlink($fromTempFile);

            return $md5;
        } catch (Exception $e) {
            return false;
        }
    }
}
