<?php

namespace app\api\controller\v1;

use app\common\controller\BaseController;

use app\common\model\TopicClass AS TopicClassModel;

use app\common\validate\TopicClassValidate;

/**
 * 话题分类
 * @package app\api\controller\v1
 * @author XieJiaWei<print_f@hotmail.com>
 * @version 1.0.0
 */
class TopicClass extends BaseController
{
    /**
     * 获取话题分类
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function index()
    {
        //获取话题分类列表
        $list = (new TopicClassModel())->getTopicClassList();

        return self::showResCode('获取成功', ['list' => $list]);
    }

    /**
     * 获取指定话题分类下的话题列表
     * @return \think\response\Json
     * @throws \app\lib\exception\BaseException
     */
    public function topic()
    {
        // 验证分类id和分页数
        (new TopicClassValidate())->goCheck();

        $list = (new TopicClassModel)->getTopic();
        return self::showResCode('获取成功', ['list' => $list]);
    }
}
