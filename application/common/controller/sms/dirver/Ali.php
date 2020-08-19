<?php

namespace app\common\controller\sms\dirver;

use AlibabaCloud\Client\AlibabaCloud;

use AlibabaCloud\Client\Exception\ClientException;

use AlibabaCloud\Client\Exception\ServerException;

use app\common\controller\sms\SmsInterface;

use app\lib\exception\BaseException;

/**
 * 阿里大于短信接口调用类
 * @package app\common\controller
 * @author XieJiaWei<print_f@hotmail.com>
 * @version 1.0.0
 */
class Ali implements SmsInterface
{
    /**
     * 配置参数
     * @var mixed
     */
    private $config = [
        //是否开启功能
        'isopen' => true,
        'accessKeyId' => '',
        'accessSecret' => '',
        //节点
        'regionId' => 'cn-hangzhou',
        //产品
        'product' => 'Dysmsapi',
        //版本号
        'version' => '2017-05-25',
        //签名名称
        'signName' => '',
        //短信模版
        'templateCode' => '',
        //验证码发送时间间隔
        'expire' => 60,
        //ip日限制发送数量
        'ipLimit' => 10
    ];

    /**
     * AliSmsController constructor.
     * @param $option array 配置参数
     * @throws ClientException
     */
    public function __construct($option)
    {
        $this->config = array_merge($this->config, $option);
        AlibabaCloud::accessKeyClient($this->config['accessKeyId'], $this->config['accessSecret'])
            ->regionId($this->config['regionId'])
            ->asDefaultClient();
    }

    /**
     * 验证功能是否开启
     * @return mixed
     */
    public function checkOpen(): bool
    {
        return $this->config['isopen'];
    }

    /**
     * 获取配置参数
     * @param $name string 配置名 为空获取全部
     * @return array
     */
    public function getConfig($name = '')
    {
        return !empty($name) ? $this->config[$name] : $this->config;
    }

    /**
     * 解析配置
     * @param $phone
     * @param $code
     * @return array
     */
    private function parseOption($phone, $code)
    {
        return [
            'query' => [
                'RegionId' => $this->config['regionId'],
                'PhoneNumbers' => $phone,
                'SignName' => $this->config['signName'],
                'TemplateCode' => $this->config['templateCode'],
                'TemplateParam' => '{"code":"' . $code . '"}',
            ],
        ];
    }

    /**
     * 发送短信
     * @param int $phone
     * @param int|string $code
     * @return array
     * @throws BaseException
     * @throws ClientException
     */
    public function sendSms($phone, $code)
    {
        try {
            $result = AlibabaCloud::rpc()
                ->product($this->config['product'])
                ->version($this->config['version'])
                ->action('SendSms')
                ->method('GET')
                ->host('dysmsapi.aliyuncs.com')
                ->options($this->parseOption($phone, $code))
                ->request();
            return $result->toArray();
        } catch (ClientException $e) {
            ApiException($e->getErrorMessage(), 30000, 200);
        } catch (ServerException $e) {
            ApiException($e->getErrorMessage(), 30000, 200);
        }
    }
}
