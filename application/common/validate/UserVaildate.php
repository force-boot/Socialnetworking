<?php

namespace app\common\validate;

class UserVaildate extends BaseVaildate
{
    protected $rule = [
        'phone' => 'require|mobile',
        'code' => 'require|number|length:4|isPefectCode',
        'username' => 'require',
        'password' => 'require|alphaDash',
        'provider' => 'require',
        'id' => 'require|integer|>:0',
        'page' => 'require|integer|>:0',
        'email' => 'require|email',
        'userpic' => 'image',
        'name' => 'require',
        'sex' => 'require|in:1,2,3',
        'age' => 'require|integer|>:0|<=:120',
        'birthday' => 'require|dateFormat:Y-m-d',
        'city' => 'require|chsDash',
        'oldpassword' => 'require',
        'newpassword' => 'require|alphaDash',
        'renewpassword' => 'require|confirm:newpassword',
        'follow_id' => 'require|integer|>:0|isUserExist',
    ];

    protected $message = [
        'phone.require' => '请填写手机号码',
        'phone.mobile' => '手机号格式不正确',
        'age' => '年龄不符合规范'
    ];

    protected $scene = [
        //发送验证码
        'sendcode' => ['phone'],
        //手机号登录
        'phonelogin' => ['phone', 'code'],
        //帐号密码登录
        'login' => ['username', 'password'],
        //第三方登录
        'otherlogin' => ['provider'],
        //获取文章
        'post' => ['id', 'page'],
        //当前用户获取全部文章
        'allpost' => ['page'],
        //绑定手机
        'bindphone' => ['phone', 'code'],
        //绑定邮箱
        'bindemail' => ['email'],
        //绑定第三方
        'bindthird' => ['provider'],
        //修改头像
        'edituserpic' => ['userpic'],
        //修改资料
        'edituserinfo' => ['name', 'sex', 'age', 'birthday', 'city'],
        //修改密码
        'repassword' => ['oldpassword', 'newpassword', 'renewpassword'],
        //关注
        'follow' => ['follow_id'],
        //取消关注
        'unfollow' => ['follow_id'],
        //互关列表
        'getfriends' => ['page'],
        //粉丝列表
        'getfans' => ['page'],
        //关注列表
        'getfollows' => ['page'],
    ];
}
