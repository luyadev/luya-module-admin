<?php

namespace luya\admin\commands;

use Curl\Curl;
use luya\admin\models\Config;
use luya\admin\Module;
use luya\admin\proxy\ClientBuild;
use luya\admin\proxy\ClientTransfer;
use luya\console\Command;
use luya\helpers\Url;
use luya\traits\CacheableTrait;
use yii\db\Connection;
use yii\di\Instance;
use yii\helpers\Json;

/**
 * Synchronise a PROD env to your locale env with files and images.
 *
 * The proxy command will ask for an url, identifier and token. The url is the url of your website in production where you have leased the token and
 * identifier inside the admin. Make sure you are using the right protocol (with or without https)!
 *
 * e.g url: `https://luya.io` or if you are using a domain with www `http://www.example.com` depending on your server configuration.
 *
 * ```sh
 * ./vendor/bin/luya admin/proxy
 * ```
 *
 * You can also provide all prompted options in order to not used an interactive mode:
 *
 * ```sh
 * ./vendor/bin/luya admin/proxy --url=https://example.com --idf=lcp58e35acb4ca69 --token=ESOH1isB3ka_dF09ozkDJewpeecGCdUw
 * ```
 *
 * ## Sync specific Table
 *
 * ```sh
 * ./vendor/bin/luya admin/proxy --table=admin_user
 * ```
 * which is equals to:
 *
 * ```sh
 * ./vendor/bin/luya admin/proxy -t=large_table
 * ```
 *
 * Using wildcard to use table with a given prefix use:
 *
 * ```sh
 * ./vendor/bin/luya admin/proxy -t=app_*
 * ```
 *
 * would only sync tables which starts with `app_*` like `app_news`, `app_articles`.
 *
 * In order to ignore certain tables its possible to negate input values with `!`:
 *
 * ```sh
 * ./vendor/bin/luya admin/proxy -t='!crawler*'
 * ```
 *
 * The above exmaple would exclude all tables starting *crawler*.
 *
 * ## Storage or DB
 *
 * In order to switch where either only the files/images or the table data should be synced use the
 * `only` argument:
 *
 * ```sh
 * ./vendor/bin/luya admin/proxy --only=storage
 * ./vendor/bin/luya admin/proxy --only=db
 * ```
 *
 * or as short code
 *
 * ```sh
 * ./vendor/bin/luya admin/proxy -o=storage
 * ./vendor/bin/luya admin/proxy -o=db
 * ```
 *
 * + storage: files and images
 * + db: database table rows
 *
 * @property Module $module
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.0
 */
class ProxyController extends Command
{
    use CacheableTrait;

    public const CONFIG_VAR_URL = 'lcpProxyUrl';

    public const CONFIG_VAR_TOKEN = 'lcpProxyToken';

    public const CONFIG_VAR_IDENTIFIER = 'lcpProxyIdentifier';

    /**
     * @inheritdoc
     */
    public $defaultAction = 'sync';

    /**
     * @var boolean Whether the isComplet sync check should be done after finish or not. If a table has a lot of traffic sometimes
     * there is a difference between the exchange of table informations (build) and transfer the data. In order to prevent
     * the exception message you can disable the strict compare mode. In order to ensure strict comparing enable $strict.
     * @deprecated Deprecated since version 4, will be removed in 5. No replacement.
     */
    public $strict = false;

    /**
     * @var string If a table option is passed only this table will be synchronised. If false by default all tables will be synced. You
     * can define multible tables ab seperating those with a comma `table1,table2,table`. In order to define only tables with start
     * with a given prefix you can use `app_*` using asterisks symbold to define wild card starts with string definitions.
     * To exclude tables you can use a `!` before the tablename e.g. `!admin_*` or multible `!admin_*,!test_*`
     */
    public $table;

    /**
     * @var string The production environment Domain where your LUYA application is running in production mode make so to use the right protocol
     * examples:
     * - https://luya.io
     * - http://www.example.com
     */
    public $url;

    /**
     * @var string The identifier you get from the Machines menu in your production env admin looks like this: lcp58e35acb4ca69
     */
    public $idf;

    /**
     * @var string The token which is used for the identifier, looks like this: ESOH1isB3ka_dF09ozkDJewpeecGCdUw
     */
    public $token;

    /**
     * @var integer Number of requests collected until they are written to the database.
     */
    public $syncRequestsCount = 10;

    /**
     * @var string Database connection (component name) where the data will be stored.
     */
    public $db = 'db';

    /**
     * @var string either `db` or `storage` are valid values.
     * @since 4.0.0
     */
    public $only;

    /**
     * @inheritdoc
     */
    public function options($actionID)
    {
        return array_merge(parent::options($actionID), ['strict', 'table', 'url', 'idf', 'token', 'syncRequestsCount', 'db', 'only']);
    }

    /**
     * @inheritdoc
     */
    public function optionAliases()
    {
        return array_merge(parent::optionAliases(), ['s' => 'strict', 't' => 'table', 'u' => 'url', 'i' => 'idf', 'tk' => 'token', 'o' => 'only']);
    }

    /**
     * Sync Proxy Data.
     *
     * @return number
     */
    public function actionSync()
    {
        $this->db = Instance::ensure($this->db, Connection::class);

        if ($this->url === null) {
            $url = Config::get(self::CONFIG_VAR_URL);

            if (!$url) {
                $url = $this->prompt('Enter the Proxy PROD env URL (e.g. https://example.com):');
                Config::set(self::CONFIG_VAR_URL, $url);
            }
        } else {
            $url = $this->url;
        }

        if ($this->idf === null) {
            $identifier = Config::get(self::CONFIG_VAR_IDENTIFIER);

            if (!$identifier) {
                $identifier = $this->prompt('Please enter the identifier:');
                Config::set(self::CONFIG_VAR_IDENTIFIER, trim($identifier));
            }
        } else {
            $identifier = $this->idf;
        }

        if ($this->token === null) {
            $token = Config::get(self::CONFIG_VAR_TOKEN);

            if (!$token) {
                $token = $this->prompt('Please enter the access token:');
                Config::set(self::CONFIG_VAR_TOKEN, trim($token));
            }
        } else {
            $token = $this->token;
        }


        $proxyUrl = Url::ensureHttp(rtrim(trim($url), '/')) . '/admin/api-admin-proxy';
        $this->outputInfo('Connect to: ' . $proxyUrl);

        $curl = new Curl();
        $curl->get($proxyUrl, ['identifier' => $identifier, 'token' => sha1($token)]);

        if ($curl->isSuccess()) {
            $this->flushHasCache();

            $this->verbosePrint($curl->response);
            $response = Json::decode($curl->response);
            $build = new ClientBuild($this, $this->db, [
                'optionStrict' => $this->strict,
                'optionTable' => $this->table,
                'syncRequestsCount' => (int)$this->syncRequestsCount,
                'buildToken' => sha1($response['buildToken']),
                'buildConfig' => $response['config'],
                'requestUrl' => $response['providerUrl'],
                'requestCloseUrl' => $response['requestCloseUrl'],
                'fileProviderUrl' => $response['fileProviderUrl'],
                'imageProviderUrl' => $response['imageProviderUrl'],
                'machineIdentifier' => $identifier,
                'machineToken' => sha1($token),
            ]);

            $process = new ClientTransfer(['build' => $build, 'only' => $this->only]);
            if ($process->start()) {
                // as the admin_config table is synced to, we have to restore the current active config which has been used.
                Config::set(self::CONFIG_VAR_IDENTIFIER, $identifier);
                Config::set(self::CONFIG_VAR_TOKEN, $token);
                Config::set(self::CONFIG_VAR_URL, $url);

                return $this->outputSuccess('Sync process has been successfully finished.');
            }
        }

        $this->clearConfig();
        $this->output($curl->response);
        return $this->outputError($curl->error_message);
    }

    private function clearConfig()
    {
        Config::remove(self::CONFIG_VAR_TOKEN);
        Config::remove(self::CONFIG_VAR_URL);
        Config::remove(self::CONFIG_VAR_IDENTIFIER);
    }

    /**
     * Cleanup all stored Config Data.
     *
     * @return number
     */
    public function actionClear()
    {
        $this->clearConfig();
        return $this->outputSuccess('Config has been cleared.');
    }
}
