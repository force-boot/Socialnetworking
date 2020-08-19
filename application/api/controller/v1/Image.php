<?php

namespace app\api\controller\v1;

use app\common\controller\BaseController;

use app\common\model\Image AS ImageModel;

/**
 * Class Image
 * @package app\api\controller\v1
 * @author XieJiaWei<print_f@hotmail.com>
 * @version 1.0.0
 */
class Image extends BaseController
{
    /**
     * 上传图片
     * @return \think\response\Json
     * @throws \app\lib\exception\BaseException
     */
    public function uploadMore()
    {
        $list = (new ImageModel())->uploadMore();
        return self::showResCode('上传成功', ['list' => $list]);
    }
}
