<?php

namespace app\api\controller\v1;

use app\common\controller\BaseController;

use app\common\model\PostClass AS PostClassModel;

use app\common\validate\TopicClassValidate;

/**
 * 文章分类
 * @package app\api\controller\v1
 * @author XieJiaWei<print_f@hotmail.com>
 * @version 1.0.0
 */
class PostClass extends BaseController
{
    /**
     * 获取文章分类列表
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function index()
    {
        //获取文章分类列表
        $list = (new PostClassModel())->getPostClassList();

        return self::showResCode('获取成功', ['list' => $list]);
    }

    /**
     * 获取指定分类下的文章
     * @return \think\response\Json
     * @throws \app\lib\exception\BaseException
     */
    public function post()
    {
        // 验证分类id和分页数
        (new TopicClassValidate())->goCheck();

        $list=(new PostClassModel)->getPost();
        return self::showResCode('获取成功',['list'=>$list]);
    }

}
