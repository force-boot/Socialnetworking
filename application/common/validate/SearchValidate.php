<?php

namespace app\common\validate;

class SearchValidate extends BaseVaildate
{
    protected $rule = [
        'keyword' => 'require|chsDash',
        'page' => 'require|integer|>:0',
    ];

    protected $message = [];

    protected $scene = [
        'think' => ['keyword']
    ];
}
