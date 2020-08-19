<?php

namespace app\common\validate;

class TopicClassValidate extends BaseVaildate
{
    protected $rule = [
        'id'=>'require|integer|>:0',
        'page'=>'require|integer|>:0',
    ];


    protected $message = [];
}
