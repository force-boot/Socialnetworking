<?php

namespace app\api\controller\v1;

use app\common\controller\BaseController;

use app\common\validate\CommentValidate;

use \app\common\model\Comment AS CommentModel;

/**
 * 用户评论
 * @package app\api\controller\v1
 * @author XieJiaWei<print_f@hotmail.com>
 * @version 1.0.0
 */
class Comment extends BaseController
{

    /**
     * 用户评论
     * @return \think\response\Json
     * @throws \app\lib\exception\BaseException
     */
    public function create()
    {
        (new CommentValidate())->goCheck();
        (new CommentModel())->comment();
        return self::showResCodeWithOutData('评论成功');
    }
}
