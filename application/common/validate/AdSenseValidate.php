<?php

namespace app\common\validate;


class AdSenseValidate extends BaseVaildate
{

    protected $rule = [
        'type' => 'require|integer|in:0,1',
    ];

}
