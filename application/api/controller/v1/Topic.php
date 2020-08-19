<?php

namespace app\api\controller\v1;

use app\common\controller\BaseController;

use app\common\model\Topic AS TopicModel;

use app\common\validate\TopicClassValidate;

/**
 * 话题模块
 * @package app\api\controller\v1
 * @author XieJiaWei<print_f@hotmail.com>
 * @version 1.0.0
 */
class Topic extends BaseController
{
    /**
     * 获取热门话题
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function index()
    {
        $list = (new TopicModel())->gethotlist();
        return self::showResCode('获取成功', ['list' => $list]);
    }

    /**
     * 获取指定话题下的文章列表
     * @return \think\response\Json
     * @throws \app\lib\exception\BaseException
     */
    public function post()
    {
        // 验证分类id和分页数
        (new TopicClassValidate())->goCheck();
        $list=(new TopicModel)->getPost();
        return self::showResCode('获取成功',['list'=>$list]);
    }
}
