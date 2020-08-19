<?php

namespace app\common\model;

use think\Model;

/**
 * 平台广告
 * @package app\common\model
 * @author XieJiaWei<print_f@hotmail.com>
 * @version 1.0.0
 */
class Adsense extends Model
{
    /**
     * 获取广告列表
     * @return array|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getList()
    {
        $param = input();
        return $this
            ->where('type', $param['type'])
            ->select();
    }
}
