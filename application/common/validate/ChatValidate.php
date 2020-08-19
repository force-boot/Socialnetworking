<?php

namespace app\common\validate;

class ChatValidate extends BaseVaildate
{
    protected $rule = [
        'to_id' => 'require|isUserExist',
        'from_userpic' => 'require',
        'type' => 'require',
        'data' => 'require',
        'client_id' => 'require'
    ];

    protected $scene = [
        'send' => ['to_id', 'from_userpic', 'type', 'data'],
        'bind' => ['type', 'client_id']
    ];
}
