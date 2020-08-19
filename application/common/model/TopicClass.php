<?php

namespace app\common\model;

use think\Model;

/**
 * 话题分类
 * @package app\common\model
 * @author XieJiaWei<print_f@hotmail.com>
 * @version 1.0.0
 */
class TopicClass extends Model
{
    /**
     * 获取所有话题分类
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getTopicClassList()
    {
        return $this->field('id,classname')
            ->where('status', 1)
            ->select();
    }

    /**
     * 关联话题
     * @return \think\model\relation\HasMany
     */
    public function topic()
    {
        return $this->hasMany('Topic');
    }

    /**
     * 获取指定话题分类下的话题（分页）
     * @return mixed
     */
    public function getTopic()
    {
        // 获取所有参数
        $param = input();
        return self::get($param['id'])
            ->topic()
            ->page($param['page'], 10)
            ->select();
    }
}
