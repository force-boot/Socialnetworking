<?php

namespace app\api\controller\v1;

use app\common\controller\BaseController;

use app\common\model\Post AS PostModel;

use app\common\validate\PostValidate;

/**
 * Class Post
 * @package app\api\controller\v1
 * @author XieJiaWei<print_f@hotmail.com>
 * @version 1.0.04
 */
class Post extends BaseController
{
    /**
     * 获取文章详情
     * @return \think\response\Json
     * @throws \app\lib\exception\BaseException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function index()
    {
        // 验证文章id
        (new PostValidate())->goCheck('detail');
        $detail = (new PostModel)->getPostDetail();
        return self::showResCode('获取成功', ['detail' => $detail]);
    }

    /**
     * 发表文章
     * @return \think\response\Json
     * @throws \app\lib\exception\BaseException
     * @throws \think\Exception
     */
    public function create()
    {
        (new PostValidate())->goCheck('create');
        (new PostModel)->createPost();
        return self::showResCodeWithOutData('发布成功');
    }

    /**
     * 获取文章评论
     * @return \think\response\Json
     * @throws \app\lib\exception\BaseException
     */
    public function comment()
    {
        // 验证文章id
        (new PostValidate())->goCheck('detail');
        $list = (new PostModel)->getComment();
        return self::showResCode('获取成功', ['list' => $list]);
    }
}
