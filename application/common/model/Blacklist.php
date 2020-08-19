<?php

namespace app\common\model;

use think\Model;

/**
 * 黑名单
 * @package app\common\model
 * @author XieJiaWei<print_f@hotmail.com>
 * @version 1.0.0
 */
class Blacklist extends Model
{
    // 自动写入时间戳
    protected $autoWriteTimestamp = true;

    /**
     * 加入黑名单
     * @return bool
     * @throws \app\lib\exception\BaseException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function addBlack()
    {
        $param = input();
        $user_id = request()->userId;
        $black_id = $param['id'];
        // 不能拉黑自己
        if ($user_id == $black_id) ApiException('非法操作', 50000, 200);
        $arr = ['user_id' => $user_id, 'black_id' => $black_id];
        // 已经存在该记录
        if ($this->where($arr)->find()) ApiException('对方已被你拉黑过', 40001, 200);
        // 直接创建
        if (!$this->create($arr)) ApiException();
        return true;
    }

    /**
     * 移出黑名单
     * @return bool
     * @throws \app\lib\exception\BaseException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function removeBlack()
    {
        $param = input();
        $user_id = request()->userId;
        $black_id = $param['id'];
        // 不能拉黑自己
        if ($user_id == $black_id) ApiException('非法操作', 50000, 200);
        $black = $this->where([
            'user_id' => $user_id,
            'black_id' => $black_id
        ])->find();
        // 记录不存在
        if (!$black) ApiException('对方已不在你的黑名单内', 40002, 200);
        // 直接删除
        if (!$black->delete()) ApiException();
        return true;
    }
}
