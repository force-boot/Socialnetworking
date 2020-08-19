<?php

namespace app\api\controller\v1;

use app\common\controller\BaseController;

use app\common\model\Adsense AS AdSenseModel;

use app\common\validate\AdSenseValidate;

/**
 * 平台广告
 * @package app\api\controller\v1
 * @author XieJiaWei<print_f@hotmail.com>
 * @version 1.0.0
 */
class AdSense extends BaseController
{
    /**
     * 获取广告列表
     * @return \think\response\Json
     * @throws \app\lib\exception\BaseException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function index()
    {
        (new AdSenseValidate())->goCheck();
        $list = (new AdSenseModel)->getList();
        return self::showResCode('获取成功', ['list' => $list]);
    }
}
