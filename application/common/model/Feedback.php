<?php

namespace app\common\model;

use think\Model;

/**
 * 用户反馈
 * @package app\common\model
 * @author XieJiaWei<print_f@hotmail.com>
 * @version 1.0.0
 */
class Feedback extends Model
{
    protected $autoWriteTimestamp = true;

    /**
     * 用户反馈
     * @return bool|void
     * @throws \app\lib\exception\BaseException
     */
    public function feedback()
    {
        $param = input();
        $data = [
            'from_id' => request()->userId,
            'to_id' => 0,
            'data' => $param['data']
        ];
        if (!$this->create($data)) return ApiException();
        return true;
    }

    /**
     * 获取用户反馈列表
     * @return array|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function feedbackList()
    {
        $page = input('page');
        $user_id = request()->userId;
        return $this
            ->where('from_id', $user_id)
            ->whereOr('to_id', $user_id)
            ->page($page, 10)
            ->select();
    }
}
