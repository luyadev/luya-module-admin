<?php

namespace luya\admin\proxy;


use Yii;
use Exception;
use Curl\Curl;
use luya\admin\file\Query;
use luya\admin\image\Item;
use luya\traits\CacheableTrait;
use luya\helpers\FileHelper;
use yii\base\BaseObject;
use yii\base\InvalidConfigException;

/**
 * Admin Proxy commands Transfer Files.
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.0
 */
class ClientTransfer extends BaseObject
{
    use CacheableTrait;

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
     * @var ClientBuild
     */
    public $build;
    
    public function start()
    {
        $this->flushHasCache();
        
        foreach ($this->build->getTables() as $name => $table) {
            /** @var \luya\admin\proxy\ClientTable $table  */
            if (!$table->isComplet()) {
                if ($this->build->optionStrict) {
                    $this->build->command->outputInfo('Rows Expected: ' . $table->getRows());
                    $this->build->command->outputInfo('Rows Downloaded: ' . $table->getContentRowCount());
                    return $this->build->command->outputError('Incomplet build, stop execution: ' . $name);
                }
            }
        }
    
        if ($this->build->db->schema instanceof \yii\db\mysql\Schema) {
            $this->build->command->outputInfo('Using local database ' . $this->build->db->createCommand('SELECT DATABASE()')->queryScalar());
        }

        foreach ($this->build->getTables() as $name => $table) {
            $table->syncData();
        }
        
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
        
        $imageCount = 0;
        
        // sync images
        foreach ((new \luya\admin\image\Query())->all() as $image) {
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
        }
        
        $this->build->command->outputInfo("[=] {$imageCount} Images downloaded.");
        
        // close the build
        $curl = new Curl();
        $curl->get($this->build->requestCloseUrl, ['buildToken' => $this->build->buildToken]);
        
        return true;
    }

    public function storageUpload($fileName, $content)
    {
        try {
            $fromTempFile = @tempnam(sys_get_temp_dir(), 'uploadFromContent');
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
