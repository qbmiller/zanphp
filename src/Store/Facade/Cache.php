<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 15/12/12
 * Time: 22:23
 */
namespace Zan\Framework\Store\Facade;

use RuntimeException;
use Zan\Framework\Foundation\Core\Config;
use Zan\Framework\Network\Connection\ConnectionManager;
use Zan\Framework\Store\NoSQL\Redis\RedisManager;

class Cache {

    private static $redis=null;

    public static function get($config, $key)
    {
        $conn = (yield ConnectionManager::getInstance()->get(self::connectionPath($config)));
        $socket = $conn->getSocket();
        self::$redis = new RedisManager($socket);
        $configKey = Config::getCache($config);
        $realKey = str_replace('%s', $key, $configKey);
        if (!empty($realKey)) {
            $result = (yield self::$redis->get($realKey['key']));
            yield $result;
        }
        $conn->release();
    }

    public static function  expire($config, $key, $expire=0)
    {
        $conn = (yield ConnectionManager::getInstance()->get(self::connectionPath($config)));
        $socket = $conn->getSocket();
        self::$redis = new RedisManager($socket);
        $configKey = Config::getCache($config);
        $realKey = str_replace('%s', $key, $configKey);
        if (!empty($realKey)) {
            $result = (yield self::$redis->expire($realKey['key'], $expire));
            yield $result;
        }
        $conn->release();
    }

    public static function set($config, $value, $key)
    {
        $conn = (yield ConnectionManager::getInstance()->get(self::connectionPath($config)));
        $socket = $conn->getSocket();
        self::$redis = new RedisManager($socket);
        $configKey = Config::getCache($config);
        $realKey = str_replace('%s', $key, $configKey);
        if (!empty($realKey)) {
            $result = (yield self::$redis->set($realKey['key'], $value, $realKey['exp']));
            yield $result;
        }
        $conn->release();
    }

    private static function connectionPath($path)
    {
        $pos= strrpos($path, '.');
        $subPath = substr($path,0, $pos);
        $config = Config::getCache($subPath);
        if(!isset($config['common'])) {
            throw new RuntimeException('connection path config not found');
        }
        return $config['common']['connection'];
    }
}