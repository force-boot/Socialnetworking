<?php

namespace app\lib\socket;

use app\common\model\User;
use GatewayWorker\Lib\Gateway;
use Workerman\Worker;
use think\facade\Cache;

/**
 * Worker 命令行服务类
 */
class Events
{

    /**
     * onConnect 事件回调
     * 当客户端连接上gateway进程时(TCP三次握手完毕时)触发
     */
    public static function onConnect($client_id)
    {
        // $data = [ 'type'=>'client_id', 'data'=>$client_id ];
        // Gateway::sendToCurrentClient(json_encode($data));
    }

    /**
     * onWebSocketConnect 事件回调
     * 当客户端连接上gateway完成websocket握手时触发
     */
    public static function onWebSocketConnect($client_id, $data)
    {
        // Gateway::sendToCurrentClient(json_encode([ 'type'=>'client_id', 'data'=>$client_id ]));
        // var_export($client_id);
    }

    /**
     * onMessage 事件回调
     * 当客户端发来数据(Gateway进程收到数据)后触发
     * @param $client_id
     * @param $data
     * @return bool|void
     */
    public static function onMessage($client_id, $data)
    {
        // 验证当前客户端是否已经绑定
        if (Gateway::getUidByClientId($client_id)) return;
        $data = json_decode($data, true);
        // 非法参数
        if (!is_array($data) || !array_key_exists('type', $data) || !array_key_exists('token', $data) || $data['type'] !== 'bind' || empty($data['token'])) return;
        $user = Cache::get($data['token']);
        if (!$user) return Gateway::sendToCurrentClient(json(
            [
                'type' => 'bind',
                'msg' => '非法token，禁止操作',
                'status' => false
            ]
        ));
        // 获取用户id
        $userId = array_key_exists('type', $user) ? $user['user_id'] : $user['id'];
        // 验证第三方是否完善了资料
        if ($userId < 1) return Gateway::sendToCurrentClient(json(
            [
                'type' => 'bind',
                'msg' => '请先完善资料',
                'status' => false
            ]
        ));
        $User = User::find($userId);
        // 验证用户是否绑定手机
//        if (!$User->phone) return Gateway::sendToCurrentClient(json(
//            [
//                'type' => 'bind',
//                'msg' => '请先绑定手机',
//                'status' => false
//            ]
//        ));
        // 验证用户状态
        if ($User->status == 0) return Gateway::sendToCurrentClient(json(
            [
                'type' => 'bind',
                'msg' => '当前用户被禁用',
                'status' => false
            ]));
        // 绑定
        Gateway::bindUid($client_id, $userId);
        return Gateway::sendToCurrentClient(json(
            [
                'type' => 'bind',
                'msg' => '绑定成功',
                'status' => true
            ]));
    }

    /**
     * onClose 事件回调 当用户断开连接时触发的方法
     */
    public static function onClose($client_id)
    {
        //GateWay::sendToAll("client[$client_id] logout\n");
    }

    /**
     * onWorkerStop 事件回调
     * 当businessWorker进程退出时触发。每个进程生命周期内都只会触发一次。
     */
    public static function onWorkerStop(Worker $businessWorker)
    {
        echo "WorkerStop\n";
    }
}