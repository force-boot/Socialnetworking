<?php

namespace app\api\controller\v1;

use app\common\controller\BaseController;

use app\common\validate\BlacklistValidate;

use app\common\model\Blacklist AS BlacklistModel;

/**
 * 黑名单
 * @package app\api\controller\v1
 * @author XieJiaWei<print_f@hotmail.com>
 * @version 1.0.0
 */
class Blacklist extends BaseController
{
    /**
     * 加入黑名单
     * @return \think\response\Json
     * @throws \app\lib\exception\BaseException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function addBlack()
    {
        (new BlacklistValidate())->goCheck();
        (new BlacklistModel())->addBlack();
        return self::showResCodeWithOutData('加入黑名单成功');
    }


    /**
     * 移除黑名单
     * @return \think\response\Json
     * @throws \app\lib\exception\BaseException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function removeBlack(){
        (new BlacklistValidate())->goCheck();
        (new BlacklistModel())->removeBlack();
        return self::showResCodeWithOutData('移除黑名单成功');
    }
}
