<?php

namespace app\common\validate;

class CommentValidate extends BaseVaildate
{
    protected $rule = [
        'fid' => 'require|integer|>:-1|isCommentExist',
        'data' => 'require|notEmpty',
        'post_id' => 'require|integer|>:0|isPostExist',
    ];
}
