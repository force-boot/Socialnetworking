<?php


namespace app\common\model;

use think\facade\Cache;

use think\Model;

/**
 * 搜索
 * @package app\common\model
 * @author XieJiaWei<print_f@hotmail.com>
 * @version 1.0.0
 */
class Search extends Model
{
    // 自动写入时间戳
    public $autoWriteTimestamp = true;

    /**
     * 获取搜索数据
     * @param int $num 获取数量 default 10  0获取所有
     * @param mixed $sortByNum 是否通过num值进行排序
     * 默认false 支持 asc or desc
     * @return array
     */
    private function getSearchData(int $num = 10, $sortByNum = false): array
    {
        $redis = redis();
        $arrays = [];
        $searchArr = $redis->sMembers('search_set');
        if (empty($searchArr)) return $arrays;
        foreach ($searchArr as $key) {
            $arrays[] = [
                'num' => $redis->sCard('user_' . $key),
                'keyword' => $redis->hGet('key_' . $key, 'keyword'),
                'time' => $redis->hGet('key_' . $key, 'time')
            ];
        }
        // 是否排序
        if (false != $sortByNum) $arrays = multiArraySort($arrays, 'num', $sortByNum);

        // 获取指定数量
        if ($num != 0) $arrays = array_slice($arrays, 0, $num);

        return $arrays;
    }

    /**
     * 获取热搜榜单
     * @return array
     */
    public function getHotRank()
    {
        //榜单数量
        $rankNum = input('?num') ? input('num') : 10;
        return $this->getSearchData($rankNum, 'desc');
    }

    /**
     * 搜索结果分析 搜索频率较高的相关文章 适当增加推荐机会
     * @throws \think\Exception
     */
    public function searchResParse()
    {
        // 获取热搜榜单
        $hot = $this->getHotRank();
        // 热搜推荐
        return $this->searchParseDataForeach($hot);
    }

    /**
     * 搜索分析数据 循环处理
     * @param array $data
     * @return bool
     * @throws \think\Exception
     */
    private function searchParseDataForeach(array $data)
    {
        $arr = [];
        foreach ($data as $value) {
            $key = md5($value['keyword']);
            // 取出数据 并删除
            $data = Cache::pull('search_res_' . $key);
            if (!$data || !is_array($data)) break;
            $arr[] = $data;
        }

        return Post::recommend($arr, 1);
    }

    /**
     * 搜索结果输出
     * @param array $select toArray后的结果集
     * @param string $keyword 关键词
     * @return array
     */
    public static function searchResOutput(array $select, string $keyword)
    {
        $redis = redis();
        // 用户是否登录 0 证明是游客搜索
        $userId = request()->userId ? request()->userId : 0;
        $key = md5($keyword);
        $redis->sAdd('search_set', $key);
        // 同一个用户 或者游客 重复搜索 都只会被记录搜索一次
        $user_search = $redis->sAdd('user_' . $key, $userId);
        if ($user_search) $redis->hMSet('key_' . $key, [
            'keyword' => $keyword,
            'time' => time()
        ]);

        // 如果用户已登录 逻辑将上升到 用户个人的文章推荐 如根据他搜索 知道他喜欢的分类等
        // 暂未完成

        // 如果缓存还存在 先输出缓存
        if ($result = cache('search_res_' . $key)) return $result;

        // 缓存不存在 将查询结果存入缓存 使用队列方式 进行文章热度分析
        Cache::set('search_res_' . $key, $select);

        return $select;
    }
}