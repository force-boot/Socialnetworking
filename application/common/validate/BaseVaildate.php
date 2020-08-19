<?php

namespace app\common\validate;

use app\common\model\Comment;
use app\common\model\Post;
use app\common\model\PostClass;
use app\common\model\Topic;
use app\common\model\User;
use app\lib\exception\BaseException;

use think\Validate;

/**
 * 基础验证器类
 * @package app\common\validate
 * @author XieJiaWei<print_f@hotmail.com>
 * @version 1.0.0
 */
class BaseVaildate extends Validate
{

    /**
     * 通用验证数据 支持验证场景
     * @param string $scene 验证场景
     * @return bool 验证失败返回json格式 {"msg":"用户名不能为空","errorCode":10000}
     * @throws BaseException
     */
    public function goCheck(string $scene = ''): bool
    {
        //获取所有请求参数
        $params = input();
        //是否需要验证场景
        $check = $scene ? $this->scene($scene)->check($params) : $this->check($params);
        if (!$check) {
            ApiException($this->getError(), 10000, 400);
        }
        return true;
    }

    /**
     * 自定义验证器规则  验证验证码
     * @param string $value
     * @param string $rule
     * @param array $data
     * @param string $field
     * @return bool|string
     */
    protected function isPefectCode(string $value, string $rule = '', array $data = [], string $field = '')
    {
        $beforeCode = cache('sendCode_' . $data['phone']);
        if (!$beforeCode) return '请重新获取验证码';
        //验证验证码
        if ($value != $beforeCode) return '验证码错误';
        return true;
    }

    /**
     * 话题是否存在 by id
     * @param $value
     * @param string $rule
     * @param string $data
     * @param string $field
     * @return bool|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    protected function isTopicExist($value, $rule = '', $data = '', $field = '')
    {
        if ($value == 0) return true;
        if (Topic::field('id')->find($value)) {
            return true;
        }
        return "该话题不存在";
    }

    /**
     * 用户是否存在 by id
     * @param $value
     * @param string $rule
     * @param string $data
     * @param string $field
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    protected function isUserExist($value, $rule = '', $data = '', $field = '')
    {
        if (User::field('id')->find($value)) return true;
        return '用户不存在';
    }

    /**
     * 不能为空
     * @param $value
     * @param string $rule
     * @param string $data
     * @param string $field
     * @return bool|string
     */
    protected function notEmpty($value, $rule = '', $data = '', $field = '')
    {
        if (empty($value)) return $field . "不能为空";
        return true;
    }

    /**
     * 文章分类是否存在 by id
     * @param $value
     * @param string $rule
     * @param string $data
     * @param string $field
     * @return bool|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    protected function isPostClassExist($value, $rule = '', $data = '', $field = '')
    {
        if (PostClass::field('id')->find($value)) {
            return true;
        }
        return "该文章分类不存在";
    }

    /**
     * 文章是否存在 by id
     * @param $value
     * @param string $rule
     * @param string $data
     * @param string $field
     * @return bool|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    protected function isPostExist($value, $rule = '', $data = '', $field = '')
    {
        if (Post::field('id')->find($value)) {
            return true;
        }
        return "该文章不存在";
    }

    /**
     * 评论是否存在 by id
     * @param $value
     * @param string $rule
     * @param string $data
     * @param string $field
     * @return bool|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    protected function isCommentExist($value, $rule = '', $data = '', $field = '')
    {
        if ($value == 0) return true;
        if (Comment::field('id')->find($value)) {
            return true;
        }
        return "回复的评论不存在";
    }


}