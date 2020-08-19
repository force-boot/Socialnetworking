<?php

namespace app\common\model;

use think\Model;

/**
 * 文章点赞
 * @package app\common\model
 * @author XieJiaWei<print_f@hotmail.com>
 * @version 1.0.0
 */
class Support extends Model
{
    /**
     * redis操作句柄
     * @var \Redis
     */
    public $redis;

    /**
     * 初始化redis
     * @param array $data
     */
    public function __construct($data = [])
    {
        $this->redis = redis();
        parent::__construct($data);
    }

    /**
     * 用户点赞/取消赞
     * @return bool|int
     * @throws \app\lib\exception\BaseException
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function userSupportPost()
    {
        $param = input();
        // 用户id
        $user_id = request()->userId;
        // 文章id
        $post_id = $param['post_id'];
        $this->redis->sAdd('post_set', $post_id);
        $redis_like = $this->redis->sAdd($post_id, $user_id);
        if ($redis_like) { // 之前沒有操作过 点赞成功
            // 文章点赞计数 +1
            $this->redis->incr('likes_count' . $post_id);
            // 给点赞的用户的 ordered set 里增加文章 ID
            $this->redis->zAdd('user:' . $user_id, time(), $post_id);
            return $this->redis->hMset('post_user_like_' . $post_id . '_' . $user_id,
                [
                    'user_id' => $user_id,
                    'post_id' => $post_id,
                    'type' => 1,
                    'create_time' => time()
                ]
            );
        }
        $key = 'post_user_like_' . $post_id . '_' . $user_id;
        // 获取当前操作状态
        $nowType = $this->redis->hGet($key, 'type');

        // 判断是否重复操作
        if ($nowType == $param['type']) ApiException('请勿重复操作', 40000, 200);

        // 修改操作类型 和操作时间
        $this->redis->hMset($key, ['type' => intval(!$nowType), 'time' => time()]);

        // 如果当前为取消状态 文章点赞数+1 else -1
        if (!$nowType) {
            return $this->redis->incr('likes_count' . $post_id);
        } else {
            return $this->redis->decr('likes_count' . $post_id);
        }
    }


    /**
     * 判断用户是否对指定文章点赞
     * @param $post_id int 文章id
     * @return array|bool|mixed|\PDOStatement|string|Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function isUserSupportPost(int $post_id)
    {
        $user_id = request()->userId;
        //记录是否存在数据库
        $support = $this->findSupportRecord(['user_id' => $user_id, 'post_id' => $post_id]);
        if ($support) return $support;
        //记录不存在 查询redis
        $key = 'post_user_like_' . $post_id . '_' . $user_id;
        $hash = $this->redis->hGetAll($key);
        if (isset($hash['type']) && $hash['type']) return $hash;

        return false;
    }

    /**
     * 获取指定文章的总点赞次数
     * @param int $post_id 文章ID
     * @return bool|float|string
     */
    public function getPostAllSupportRecord(int $post_id)
    {
        return $this->where('post_id', $post_id)->count() + $this->redis->get('likes_count' . $post_id);
    }

    /**
     * 删除点赞记录 by user_id and post_id or primaryKey
     * @param array|int
     * @return int
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function deleteSupportRecord($data)
    {
        //通过post_id 和user_id 删除记录
        if (is_array($data) && array_key_exists('user_id', $data) && array_key_exists('post_id', $data)) {
            return $this->where(['user_id' => $data['user_id'], 'post_id' => $data['post_id']])
                ->delete();
        }
        //通过主键删除
        if (is_numeric($data)) return $this->delete($data);

        return false;
    }

    /**
     * 查找点赞记录 by user_id and post_id or primaryKey
     * @param array|int $data
     * @return array|bool|mixed|\PDOStatement|string|Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function findSupportRecord($data)
    {
        //通过post_id 和user_id 查找记录
        if (is_array($data) && array_key_exists('user_id', $data) && array_key_exists('post_id', $data)) {
            return $this->where(['user_id' => $data['user_id'], 'post_id' => $data['post_id']])
                ->find();
        }
        //通过主键查找
        if (is_numeric($data)) return $this->find($data);

        return false;
    }


    /**
     * 点赞记录回滚同步数据库
     * @return \think\Collection|mixed
     * @throws \Exception
     */
    public function supportHandle()
    {
        $liked_posts = $this->redis->scard('post_set');
        $create = [];
        for ($i = 0; $i < $liked_posts; $i++) {
            $post_id = $this->redis->spop('post_set');
            // 根据上面取出的文章 ID, 查看这篇文章的 set 里共有多少个用户操作过
            $users = $this->redis->scard($post_id);
            // 有多少用户, 就循环多少次
            for ($j = 0; $j < $users; $j++) {
                // 取出一个给这篇文章点赞的用户
                $user_id = $this->redis->spop($post_id);
                $key = 'post_user_like_' . $post_id . '_' . $user_id;
                // 操作类型
                $type = $this->redis->hget($key, 'type');
                // 操作时间
                $time = $this->redis->hget($key, 'time');
                // 判断是否已经点过赞
                $support = $this->findSupportRecord(['user_id' => $user_id, 'post_id' => $post_id]);
                // 数据库已存在 并且用户提交了取消操作 删除数据库记录
                if (($support && !$type) || ($support && $type)) {
                    $this->deleteSupportRecord(['user_id' => $user_id, 'post_id' => $post_id]);
                }
                if (!$support && $type) {
                    $create[] = ['user_id' => $user_id, 'post_id' => $post_id, 'create_time' => $time];
                }
            }
            cache('post_user_like_' . $post_id . '_' . $user_id, null);
            cache('likes_count' . $post_id, null);
        }
        cache('post_set', null);
        //写入数据库
        return !empty($create) ? $this->saveAll($create) : false;
    }
}
