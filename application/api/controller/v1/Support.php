<?php

namespace app\api\controller\v1;

use app\common\controller\BaseController;
use app\common\validate\SupportValidate;
use \app\common\model\Support AS SupportModel;

class Support extends BaseController
{
    /**
     * 用户点赞文章
     * @return \think\response\Json
     * @throws \app\lib\exception\BaseException
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function index()
    {
        (new SupportValidate())->goCheck();
        (new SupportModel())->UserSupportPost();
        return self::showResCodeWithOutData('ok');
    }
}
