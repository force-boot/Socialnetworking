<?php

namespace app\api\controller\v1;

use app\common\controller\BaseController;

use app\common\model\Feedback AS FeedbackModel;

use app\common\validate\FeedbackValidate;

/**
 * 用户反馈
 * @package app\api\controller\v1
 * @author XieJiaWei<print_f@hotmail.com>
 * @version 1.0.0
 */
class Feedback extends BaseController
{
    /**
     * 反馈信息
     * @return \think\response\Json
     * @throws \app\lib\exception\BaseException
     */
    public function create()
    {
        (new FeedbackValidate())->goCheck('feedback');
        (new FeedbackModel())->feedback();
        return self::showResCodeWithOutData('反馈成功');
    }

    /**
     * 获取用户反馈列表
     * @return \think\response\Json
     * @throws \app\lib\exception\BaseException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function index()
    {
        (new FeedbackValidate())->goCheck('feedbacklist');
        $list = (new FeedbackModel())->feedbackList();
        return self::showResCode('获取成功', ['list' => $list]);
    }
}
