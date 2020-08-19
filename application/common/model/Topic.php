<?php

namespace app\common\model;

use think\Model;

/**
 * 话题
 * @package app\common\model
 * @author XieJiaWei<print_f@hotmail.com>
 * @version 1.0.0
 */
class Topic extends Model
{
    /**
     * 获取热门话题
     * @return array|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function gethotlist()
    {
        return $this
            ->where('type', 1)
            ->limit(10)
            ->select();
    }

    /**
     * 关联文章
     * @return \think\model\relation\BelongsToMany
     */
    public function post()
    {
        return $this->belongsToMany('Post', 'topic_post');
    }

    /**
     * 获取指定话题下的文章（分页）
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getPost()
    {
        // 获取所有参数
        $param = input();
        $select = self::get($param['id'])->post()->with(['user' => function ($query) {
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

    /**
     * 根据标题搜索话题
     * @return array|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function search()
    {
        // 获取所有参数
        $param = input();
        return $this
            ->where('title', 'like', '%' . $param['keyword'] . '%')
            ->page($param['page'], 10)
            ->select();
    }
}
