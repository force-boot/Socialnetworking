<?php

namespace app\common\validate;

class FeedbackValidate extends BaseVaildate
{
    protected $rule = [
        'data' => 'require|notEmpty',
        'page'=>'require|integer|>:0'
    ];

    protected $scene = [
        'feedback' => ['data'],
        'feedbacklist'=>['page']
    ];
}
