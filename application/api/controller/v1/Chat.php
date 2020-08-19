<?php

namespace app\api\controller\v1;

use app\common\controller\BaseController;

use app\common\validate\ChatValidate;

use GatewayWorker\Lib\Gateway;

use think\facade\Cache;
use think\Request;

/**
 * 用户聊天
 * @package app\api\controller\v1
 * @author XieJiaWei<print_f@hotmail.com>
 * @version 1.0.0
 */
class Chat extends BaseController
{
    /**
     * 初始化registerAddress
     */
    public function __construct()
    {
        parent::__construct();
        Gateway::$registerAddress = config('gateway_worker.registerAddress');
    }

    /**
     * 发送信息
     * @param Request $request
     * @return \think\response\Json
     * @throws \app\lib\exception\BaseException
     */
    public function send(Request $request)
    {
        // 验证数据是否合法
        (new ChatValidate())->goCheck('send');
        // 组织数据
        $data = $this->resdata($request);
        $to_id = $request->to_id;
        // 验证对方用户是否在线
        if (Gateway::isUidOnline($to_id)) {
            // 直接发送
            Gateway::sendToUid($to_id, json($data));
            // 写入数据库
            // 返回发送成功
            return self::showResCodeWithOutData('ok');
        }
        // 不在线，写入消息队列
        // 获取之前消息
        $Cache = cache('userchat_' . $to_id);
        if (!$Cache || !is_array($Cache)) $Cache = [];
        $Cache[] = $data;
        // 写入数据库
        // 写入消息队列（含id）
        cache('userchat_' . $to_id, $Cache);
        return self::showResCodeWithOutData('ok', 200);
    }

    /**
     * 获取未接收信息
     * @param Request $request
     * @return \think\response\Json|void
     */
    public function get(Request $request)
    {
        // 判断当前用户是否在线
        if (!Gateway::isUidOnline($request->userId)) return;
        // 获取并清除所有未接收信息
        $Cache = Cache::pull('userchat_' . $request->userId);
        if (!$Cache || !is_array($Cache)) return;
        // 开始推送
        return self::showResCode('ok', $Cache);
    }

    /**
     * 绑定上线
     * @param Request $request
     * @return \think\response\Json
     * @throws \app\lib\exception\BaseException
     */
    public function bind(Request $request)
    {
        // 验证当前用户是否绑定手机号，状态等信息，验证数据合法性
        (new ChatValidate)->goCheck('bind');
        $userId = $request->userId;
        $client_id = $request->client_id;
        // 验证client_id合法性
        if (!Gateway::isOnline($client_id)) return ApiException('clientId不合法');
        // 验证当前客户端是否已经绑定
        if (Gateway::getUidByClientId($client_id)) return ApiException('已被绑定');
        // 直接绑定
        Gateway::bindUid($request->client_id, $userId);
        // 返回成功
        return self::showResCode('绑定成功', ['type' => 'bind', 'status' => true]);
    }

    /**
     * 组织数据
     * @param $request
     * @return array
     */
    public function resdata($request)
    {
        return [
            'to_id' => $request->to_id,
            'from_id' => $request->userId,
            'from_userpic' => $request->from_userpic,
            'type' => $request->type,
            'data' => $request->data,
            'time' => time()
        ];
    }
}
