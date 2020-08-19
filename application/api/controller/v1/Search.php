<?php

namespace app\api\controller\v1;

use app\common\controller\BaseController;

use app\common\validate\SearchValidate;

use app\common\model\Topic AS TopicModel;

use app\common\model\Post AS PostModel;

use app\common\model\User AS UserModel;

use app\common\model\Search AS SearchModel;

/**
 * 搜索
 * @package app\api\controller\v1
 * @author XieJiaWei<print_f@hotmail.com>
 * @version 1.0.0
 */
class Search extends BaseController
{
    /**
     * 关键词联想
     * @return \think\response\Json
     * @throws \app\lib\exception\BaseException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function keywordThink()
    {
        (new SearchValidate())->goCheck('think');
        $list = (new PostModel())->keywordThink();
        return self::showResCode('获取成功', ['list' => $list]);
    }

    /**
     * 热搜榜单
     */
    public function hotRank()
    {
        $list = (new SearchModel())->getHotRank();
        return self::showResCode('获取成功', ['list' => $list]);
    }

    /**
     * 搜索话题
     * @return \think\response\Json
     * @throws \app\lib\exception\BaseException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function topic()
    {
        (new SearchValidate())->goCheck();
        $list = (new TopicModel())->search();
        return self::showResCode('获取成功', ['list' => $list]);
    }

    /**
     * 搜索文章
     * @return \think\response\Json
     * @throws \app\lib\exception\BaseException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function post()
    {
        (new SearchValidate())->goCheck();
        $list = (new PostModel())->search();
        return self::showResCode('获取成功', ['list' => $list]);
    }

    /**
     * 搜索用户
     * @return \think\response\Json
     * @throws \app\lib\exception\BaseException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function user()
    {
        (new SearchValidate())->goCheck();
        $list = (new UserModel())->search();
        return self::showResCode('获取成功', ['list' => $list]);
    }
}
