<?php

namespace app\api\controller\v1;

use app\common\controller\BaseController;

use app\common\validate\UpdateValidate;

use \app\common\model\Update AS UpdateModel;

/**
 * 版本更新
 * @package app\api\controller\v1
 * @author XieJiaWei<print_f@hotmail.com>
 * @version 1.0.0
 */
class Update extends BaseController
{
    /**
     * 检测版本更新
     * @return \think\response\Json
     * @throws \app\lib\exception\BaseException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function index()
    {
        (new UpdateValidate())->goCheck();
        $res = (new UpdateModel())->appUpdate();
        return self::showResCode('ok', $res);
    }
}
