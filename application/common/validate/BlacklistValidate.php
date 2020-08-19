<?php

namespace app\common\validate;

class BlacklistValidate extends BaseVaildate
{
    protected $rule = [
        'id'=>'require|integer|>:0|isUserExist'
    ];
}
