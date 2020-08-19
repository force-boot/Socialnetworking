<?php

namespace app\common\model;

use think\Model;

/**
 * 文章表
 * @package app\common\model
 * @author XieJiaWei<print_f@hotmail.com>
 * @version 1.0.0
 */
class Post extends Model
{
    //自动写入时间戳
    protected $autoWriteTimestamp = true;

    /**
     * 关联图片表
     * @return \think\model\relation\BelongsToMany
     */
    public function images()
    {
        return $this->belongsToMany('Image', 'post_image');
    }

    /**
     * 关联用户表
     * @return \think\model\relation\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('User');
    }

    /**
     * 关联分享
     * @return \think\model\relation\BelongsTo
     */
    public function share()
    {
        return $this->belongsTo('Post', 'share_id', 'id');
    }

    /**
     * 获取文章详情
     * @return array|\PDOStatement|string|Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getPostDetail()
    {
        // 获取所有参数
        $param = input();
        return $this->with(['user' => function ($query) {
            return $query->field('id,username,userpic');
        }, 'images' => function ($query) {
            return $query->field('url');
        }, 'share'])->find($param['id']);
    }

    /**
     * 发布文章
     * @return bool
     * @throws \think\Exception
     */
    public function createPost()
    {
        // 获取所有参数
        $params = input();
        $userModel = new User();
        // 获取用户id
        $user_id = request()->userId;
        $currentUser = $userModel->get($user_id);
        $path = $currentUser->userinfo->city;
        // 发布文章
        $title = mb_substr($params['text'], 0, 30);
        $post = $this->create([
            'user_id' => $user_id,
            'title' => $title,
            'titlepic' => '',
            'content' => $params['text'],
            'path' => $path ? $path : '未知',
            'type' => 0,
            'post_class_id' => $params['post_class_id'],
            'share_id' => 0,
            'isopen' => $params['isopen']
        ]);
        // 关联图片
        $imglistLength = count($params['imglist']);
        if ($imglistLength > 0) {
            $ImageModel = new Image();
            $imgidarr = [];
            for ($i = 0; $i < $imglistLength; $i++) {
                if ($ImageModel->isImageExist($params['imglist'][$i]['id'], $user_id)) {
                    $imgidarr[] = $params['imglist'][$i]['id'];
                }
            }
            // 发布关联
            if (count($imgidarr) > 0) $post->images()->attach($imgidarr, ['create_time' => time()]);
        }
        // 返回成功
        return true;
    }

    /**
     * 根据标题搜索文章
     * @return array|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function search()
    {
        // 获取所有参数
        $param = input();
        $result = [];
        if (!cache('search_res_' . md5($param['keyword']))) {
            $result = $this->where('title', 'like', '%' . $param['keyword'] . '%')->with(['user' => function ($query) {
                return $query->field('id,username,userpic');
            }, 'images' => function ($query) {
                return $query->field('url');
            }, 'share'])
                ->page($param['page'], 10)
                ->select()
                ->toArray();
        }

        return Search::searchResOutput($result, $param['keyword']);
    }

    /**
     * 关键词联想
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function keywordThink()
    {
        // 获取所有参数
        $param = input();

        // 缓存有效期
        $cacheTime = 604800;

        // 搜索文章标题
        $post = $this->cache(true, $cacheTime)
            ->where('title', 'like', '%' . $param['keyword'] . '%')
            ->field('title')
            ->limit(10)
            ->order('id desc')
            ->select()
            ->toArray();

        // 搜索用户
        $user = User::cache(true, $cacheTime)
            ->where('username', 'like', '%' . $param['keyword'] . '%')
            ->field('username')
            ->limit(5)
            ->order('id desc')
            ->select()
            ->toArray();

        return array_merge($post, $user);
    }

    /**
     * 关联评论
     * @return Model|\think\model\relation\HasMany
     */
    public function comment()
    {
        return $this->hasMany('Comment');
    }


    /**
     * 获取评论
     * @return mixed
     */
    public function getComment()
    {
        $params = request()->param();
        return self::get($params['id'])->comment()->with([
            'user' => function ($query) {
                return $query->field('id,username,userpic');
            }
        ])->select();
    }

    /**
     * 文章推荐
     * @param int|array $post 操作的文章 支持数组和 文章id
     * @param int $level 推荐等级 等级越高 上热门的几率越高
     * @return bool
     * @throws \think\Exception
     */
    public static function recommend($post, $level = 1): bool
    {
        // 计算分数
        $recCount = randomFloat(0, $level, 3);

        // 自增文章推荐分数
        $setInc = function ($id) use ($post, $recCount) {
            return self::where('id', $id)->setInc('recommend', $recCount);
        };

        // 文章id
        if (is_numeric($post)) return $setInc($post);

        // 非法参数
        if (!is_array($post)) return false;

        // 一维数组
        if (count($post) == count($post, 1) && isset($post['id'])) return $setInc($post['id']);

        // 多维数组
        return self::multiArrayRecommend($post, $setInc);
    }

    /**
     * 文章推荐 多维数组处理
     * @param array $array
     * @param \Closure
     * @return bool
     */
    private static function multiArrayRecommend(array $array, \Closure $setInc): bool
    {
        foreach (threeArrayToTwo($array) as $key => $value) {
            $setInc($value['id']);
        }
        return true;
    }
}
