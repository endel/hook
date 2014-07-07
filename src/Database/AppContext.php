<?php namespace API\Database;

use API\Model\App as App;
use API\Model\AppKey as AppKey;

/**
 * AppContext
 * @author Endel Dreyer <edreyer@doubleleft.com>
 */
class AppContext
{
    protected static $app_key;

    /**
     * setKey
     *
     * @param mixed $app_id
     * @param string $key
     *
     * @return API\Model\AppKey
     */
    public static function validateKey($app_id, $key) {
        if ($app_key = AppKey::where('app_id', $app_id)->where('key', $key)->first()) {
            return static::setKey($app_key);
        }
    }

    public static function setKey($app_key) {
        static::$app_key = $app_key;
        AppContext::setPrefix($app_key->app_id);
        return static::$app_key;
    }

    public static function getKey() {
        return static::$app_key;
    }

    public static function setPrefix($prefix) {
        $prefix = 'app' . $prefix . '_';

        // set database prefix
        $connection = \DLModel::getConnectionResolver()->connection();
        $connection->setTablePrefix($connection->getTablePrefix() . $prefix);

        // set cache prefix
        $connection->getCacheManager()->setPrefix($prefix);
    }

    /**
     * getPrefix
     *
     * @return string
     */
    public static function getPrefix() {
        $connection = \DLModel::getConnectionResolver()->connection();
        return $connection->getTablePrefix();
    }

    /**
     * migrate
     *
     * Migrate core application schema.
     */
    public static function migrate() {
        $connection = \DLModel::getConnectionResolver()->connection();
        if ($connection->getPdo()) {
            $builder = $connection->getSchemaBuilder();
            if (!$builder->hasTable('modules')) {
                foreach (glob(__DIR__ . '/../../migrations/app/*.php') as $file) {
                    $migration = require($file);
                    $builder->create($connection->getTablePrefix() . key($migration), current($migration));
                }
            }
        }
    }

}
