<?php

namespace app\common\validate;

class UpdateValidate extends BaseVaildate
{
    protected $rule = [
        'ver' => 'require|notEmpty'
    ];
}
