<?php

namespace app\common\controller\store;

/**
 * 资源存储 目前只支持本地，阿里OSS
 * @method static string upload(string $fileName, string $filePath)
 * @package app\common\controller\oss
 * @author XieJiaWei<print_f@hotmail.com>
 * @version 1.0.0
 */
class Store
{
    /**
     * @var object 保存实例
     */
    protected static $instance = [];

    /**
     * 配置参数
     * @var array
     */
    protected static $config = [];

    /**
     * 当前存储类型
     * @var string
     */
    protected static $type;

    /**
     * 初始化存储
     * @return object
     */
    public static function init()
    {
        //获取当前存储类型
        self::$type = config('api.store.type');
        //获取配置参数
        self::$config = config('api.store.' . self::$type);
        $className = "\\app\\common\\controller\\store\\dirver\\" . ucwords(self::$type);
        $name = md5(serialize(self::$config) . self::$type);
        if (!isset(self::$instance[$name])) {
            self::$instance[$name] = new $className(self::$config);
        }
        return self::$instance[$name];
    }

    /**
     * 静态调用
     * @param $method
     * @param $param
     * @return mixed
     */
    public static function __callStatic($method, $param)
    {
        return call_user_func_array([self::init(), $method], $param);
    }
}