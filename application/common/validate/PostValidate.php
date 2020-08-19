<?php

namespace app\common\validate;

class PostValidate extends BaseVaildate
{
    protected $rule = [
        'text' => 'require',
        'imglist' => 'require|array',
        'isopen' => 'require|in:0,1',
        'topic_id' => 'require|integer|>:0|isTopicExist',
        'post_class_id' => 'require|integer|>:0|isPostClassExist',
        'id' => 'require|integer|>:0',
    ];

    /**
     * 定义错误信息
     * 格式：'字段名.规则名'    =>    '错误信息'
     *
     * @var array
     */
    protected $message = [];


    protected $scene = [
        //发表文章
        'create' => ['text', 'imglist', 'token', 'isopen', 'topic_id', 'post_class_id'],
        //文章详情
        'detail' => ['id']
    ];
}
