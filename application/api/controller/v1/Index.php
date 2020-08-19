<?php


namespace app\api\controller\v1;


use think\Db;

class Index
{
    public $redis;

    public $num = 1;

    public function zan()
    {
        $param = input();
        $post_id = $param['post_id'];
        $user_id = $param['uid'];


    }

    public function index()
    {
        $this->redis = redis();
        if (input('ty') == 'show') {
            dump( (new \app\common\model\Support)->getPostAllSupportRecord(2));
            return;
        }
        if (input('ty') == 'h') {
           (new \app\common\model\Support)->supportHandle();
           return;
        }
        $param = input();
        // 获得用户id
        //文章id
        $post_id = $param['post_id'];
        $user_id = $param['uid'];
        $this->redis->sadd('post_set', $post_id);
        $mysql_like = Db::table('support')
            ->where(['user_id' => $user_id, 'post_id' => $post_id])
            ->find();
        $redis_like = $this->redis->sadd($post_id, $user_id);
        // 如果 Mysql 中没有记录, 且 Redis 添加成功, 点赞成功
        if (empty($mysql_like) && $redis_like) {
            // 将这篇文章的点赞计数 加一
            $this->redis->incr('likes_count' . $post_id);
            // 给点赞的用户的 ordered set 里增加文章 ID
            $this->redis->zadd('user:' . $user_id, time(), $post_id);
            // 用 hash 保存每一个赞的快照
            $this->redis->hmset('post_user_like_' . $post_id . '_' . $user_id,
                [
                    'user_id' => $user_id,
                    'post_id' => $post_id,
                    'type' => $type,
                    'time' => time()
                ]
            );
            //返回点赞成功
            return json([
                'code' => 200,
                'msg' => 'LIKE',
            ]);
            // 反之, 不管是 Mysql 中还是 Redis 中有过点赞记录, 此次操作均被视为取消点赞
        } else {
            // 将这篇文章的点赞计数减一
            $this->redis->decr('likes_count' . $post_id);
            // 从这篇文章的 set 中, 删除当前用户 ID
            $this->redis->srem($post_id, $user_id);
            // 从当前用户赞的文章集合中, 删除这篇文章
            $this->redis->zrem('user:' . $user_id, $post_id);
            // 从 mysql 中删除这条点赞记录
            Db::table('support')
                ->where('post_id', $post_id)
                ->where('user_id', $user_id)
                ->delete();
            // 返回为取消点赞
            return json([
                'code' => 202,
                'msg' => 'UNLIKE',
            ]);
        }
    }

    public function show($id)
    {
        $like_counts = 0;
        // 获取 Redis 中的点赞数
        $count_in_redis = $this->redis->get('likes_count' . $id);
        if (!is_null($count_in_redis)) {
            $like_counts += $count_in_redis;
        }
        // 获取 Mysql 的点赞数
        $count_in_mysql = \app\common\model\Support::where('post_id', $id)->find();
        if (!empty($count_in_mysql)) {
            // 加和
            $like_counts += $count_in_mysql->count;
        }
        return $like_counts;
    }

    public function handle()
    {
        // 求出 Redis 中共有多少篇文章被点赞了, 这里得到是一个整数值
        $liked_posts = $this->redis->scard('post_set');
        // 有多少篇文章被赞, 就循环多少次
        for ($i = 0; $i < $liked_posts; $i++) {
            $post_id = $this->redis->spop('post_set');
            // 根据上面取出的文章 ID, 查看这篇文章的 set 里共有多少个用户点赞
            $users = $this->redis->scard($post_id);
            // 有多少用户, 就循环多少次
            for ($j = 0; $j < $users; $j++) {
                // 取出一个给这篇文章点赞的用户
                $user_id = $this->redis->spop($post_id);
                $key = 'post_user_like_' . $post_id . '_' . $user_id;
                $type = $this->redis->hget($key, 'type');
                // 把信息存入 user_like_post 表, 也就是保存点赞的具体细节
                Db::table('support')->insert([
                    'user_id' => $user_id,
                    'post_id' => $post_id,
                    'type' => $type,
                    'create_time' => time()
                ]);
            }
        }
        // 清空缓存
        //$this->redis->flushDB();
    }
}