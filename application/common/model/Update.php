<?php

namespace app\common\model;

use think\Model;

/**
 * 版本更新
 * @package app\common\model
 * @author XieJiaWei<print_f@hotmail.com>
 * @version 1.0.0
 */
class Update extends Model
{
    protected $autoWriteTimestamp = true;

    /**
     * 检测更新
     * @return array|\PDOStatement|string|Model|null
     * @throws \app\lib\exception\BaseException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function appUpdate()
    {
        $version = input('ver');
        $res = $this->where('status', 1)
            ->order('create_time', 'desc')
            ->find();
        // 无记录
        if (!$res) ApiException('暂无更新版本');
        if ($res['version'] == $version) ApiException('暂无更新版本');
        return $res;
    }
}
