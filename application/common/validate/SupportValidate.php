<?php

namespace app\common\validate;

class SupportValidate extends BaseVaildate
{
    protected $rule = [
        'post_id' => 'require|integer|>:0',
        'type' => 'require|in:0,1'
    ];
}
