<?php

namespace app\common\model;

use think\Model;

/**
 * 话题分类
 * @package app\common\model
 * @author XieJiaWei<print_f@hotmail.com>
 * @version 1.0.0
 */
class PostClass extends Model
{
    /**
     * 获取所有文章分类
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getPostClassList()
    {
        return $this->field('id,classname')
            ->where('status', 1)
            ->select();
    }

    /**
     * 关联文章模型
     * @return \think\model\relation\HasMany
     */
    public function post()
    {
        return $this->hasMany('Post');
    }


    /**
     * 获取指定分类下的文章（分页）
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getPost()
    {
        // 获取所有参数
        $param = input();
        $select = self::get($param['id'])->post()->with([
            'user' => function ($query) {
                return $query->field('id,username,userpic');
            }, 'images' => function ($query) {
                return $query->field('url');
            }, 'share'])
            ->page($param['page'], 10)
            ->select();
        foreach ($select as $key => $value) {
            $select[$key]['support'] = (new Support())->isUserSupportPost($value->id);
        }
        return $select;
    }
}
