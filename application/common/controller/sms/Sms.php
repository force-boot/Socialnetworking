<?php


namespace app\common\controller\sms;

/**
 * 短信发送类
 * @method mixed sendSms(string $phone, int $code);
 * 发送短信
 * @method mixed getConfig(string $name = '');
 * 获取当前配置
 * @method bool checkOpen();
 * 功能是否开启
 * @package app\common\controller\sms
 * @author XieJiaWei<print_f@hotmail.com>
 * @version 1.0.0
 */
class Sms
{
    /**
     * @var object 保存操作实例
     */
    protected static $instance = [];

    /**
     * 配置参数
     * @var array
     */
    protected static $config = [];

    /**
     * 初始化
     * @return object
     */
    public static function init()
    {
        //获取当前短信服务商名称
        $name = config('api.sms.type');
        //获取配置参数
        self::$config = config('api.sms.' . $name);
        $className = "\\app\\common\\controller\\sms\\dirver\\" . ucwords($name);
        $id = md5(serialize(self::$config) . $name);
        if (!isset(self::$instance[$id])) {
            self::$instance[$id] = new $className(self::$config);
        }
        return self::$instance[$id];
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