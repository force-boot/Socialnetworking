<?php


namespace app\common\controller\sms;

/**
 * Interface SmsInterface
 * @package app\common\controller\sms
 */
interface SmsInterface
{
    /**
     * 发送短信
     * @param $phone
     * @param $code
     * @return mixed
     */
    public function sendSms($phone, $code);

    /**
     * 获取当前配置信息
     * @param string $name
     * @return mixed
     */
    public function getConfig(string $name);

    /**
     * 验证功能是否开启
     * @return bool
     */
    public function checkOpen() :bool ;
}