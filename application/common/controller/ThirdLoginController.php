<?php

namespace app\common\controller;

use anerg\OAuth2\OAuth;
use app\lib\exception\BaseException;
use think\facade\Cache;

/**
 * 第三方登录类
 * @package app\common\controller
 * @author XieJiaWei<print_f@hotmail.com>
 * @version 1.0.0
 */
class ThirdLoginController
{
    /**
     * @var OAuth|Object 第三方登录扩展类对象
     */
    public $oAuth = null;

    /**
     * @var array 配置参数
     */
    public $config = [];

    /**
     * 初始化配置
     * @param string $type 登录类型
     */
    public function __construct(string $type)
    {
        $type = $type == 'sinaweibo' ? 'weibo' : $type;
        //获取配置参数
        $this->config = config('api.thirdLogin.' . $type);
        //生成唯一state
        $this->config['state'] = createUniqueKey($type);
        //保存state 用于验证合法性
        cache($this->config['state'], $this->config['state'], 360);
        //获取第三方登录扩展类对象
        $this->oAuth = OAuth::$type($this->config);
    }

    /**
     * 获取第三方登录链接
     * @return string
     */
    public function getUrl(): string
    {
        return $this->oAuth
            ->setDisplay(request()->isMobile() ? 'moblie' : 'pc')
            ->getRedirectUrl();
    }

    /**
     * 获取第三方用户信息 (有unionid和openid)
     * @return array
     */
    public function getUserInfo(): array
    {
        return $this->oAuth->userinfo();
    }

    /**
     * 获取第三方登录用户信息（接口原始数据 和官方文档一致）
     * @return array
     */
    public function getUserInfoRaw(): array
    {
        return $this->oAuth->userinfoRaw();
    }

    /**
     * 获取第三方openid
     * @return string
     */
    public function getOpenId(): string
    {
        return $this->oAuth->openid();
    }

    /**
     * 获取第三方access_token (有expires_in)
     * @return array
     */
    public function getToken(): array
    {
        return $this->oAuth->getToken();
    }

    /**
     * 验证参数合法性
     * @param array $param
     * @return bool
     * @throws BaseException
     */
    public function checkParam(array $param): bool
    {
        if (!isset($param['code']) || !isset($param['state'])) {
            ApiException('请求参数不合法', 10000, 400);
        }
        return Cache::pull($param['state']);
    }

}