<?php

namespace app\api\controller\v1;

use app\common\controller\BaseController;

use app\common\validate\UserVaildate;

use app\common\model\User as UserModel;

/**
 * 用户模块
 * @package app\api\controller\v1
 * @author XieJiaWei<print_f@hotmail.com>
 * @version 1.0.0
 */
class User extends BaseController
{
    /**
     * 发送验证码
     * @return \think\response\Json
     * @throws \AlibabaCloud\Client\Exception\ClientException
     * @throws \app\lib\exception\BaseException
     */
    public function sendCode()
    {
        // 验证参数
        (new UserVaildate())->goCheck('sendcode');
        // 发送验证码
        (new UserModel())->sendCode();

        return self::showResCodeWithOutData('发送成功');
    }

    /**
     * 手机号登录
     * @return \think\response\Json
     * @throws \app\lib\exception\BaseException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function phoneLogin()
    {
        // 验证登录信息
        (new UserVaildate())->goCheck('phonelogin');
        //手机登录
        $token = (new UserModel())->phoneLogin();

        return self::showResCode('登录成功', ['token' => $token]);
    }

    /**
     * 帐号密码登录
     * @return \think\response\Json
     * @throws \app\lib\exception\BaseException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function login()
    {
        // 验证登录信息
        (new UserVaildate())->goCheck('login');
        // 登录
        $token = (new UserModel())->login();

        return self::showResCode('登录成功', ['token' => $token]);
    }


    /**
     * 第三方登录
     * @return \think\response\Json
     * @throws \app\lib\exception\BaseException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function thirdLogin()
    {
        // 验证登录信息
        (new UserVaildate())->goCheck('otherlogin');
        // 获取结果
        $res = (new UserModel())->thirdLogin();

        return self::showResCode('登录成功', $res);
    }

    /**
     * 注销登录
     * @return \think\response\Json
     * @throws \app\lib\exception\BaseException
     */
    public function logout()
    {
        (new UserModel())->logout();

        return self::showResCodeWithOutData('注销登录成功');
    }

    /**
     * 用户发布文章列表 （别人查看）
     * @return \think\response\Json
     * @throws \app\lib\exception\BaseException
     */
    public function post()
    {
        (new UserVaildate())->goCheck('post');
        $list = (new UserModel())->getPostList();
        return self::showResCode('获取成功', ['list' => $list]);
    }

    /**
     * 获取当前用户下的所有文章
     * @return \think\response\Json
     * @throws \app\lib\exception\BaseException
     */
    public function allPost()
    {
        (new UserVaildate())->goCheck('allpost');
        $list = (new UserModel())->getAllPostList();
        return self::showResCode('获取成功', ['list' => $list]);
    }

    /**
     * 绑定手机
     * @return \think\response\Json
     * @throws \app\lib\exception\BaseException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function bindPhone()
    {
        (new UserVaildate())->goCheck('bindphone');
        // 绑定
        (new UserModel())->bindPhone();
        return self::showResCodeWithOutData('绑定成功');
    }

    /**
     * 绑定邮箱
     * @return \think\response\Json
     * @throws \app\lib\exception\BaseException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function bindEmail()
    {
        (new UserVaildate())->goCheck('bindemail');
        // 绑定
        (new UserModel())->bindEmail();
        return self::showResCodeWithOutData('绑定成功');
    }

    /**
     * 绑定第三方
     * @return \think\response\Json
     * @throws \app\lib\exception\BaseException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function bindThird()
    {
        (new UserVaildate())->goCheck('bindthird');
        (new UserModel())->bindThird();
        return self::showResCodeWithOutData('绑定成功');
    }

    /**
     * 修改头像
     * @return \think\response\Json
     * @throws \app\lib\exception\BaseException
     */
    public function editUserPic()
    {
        (new UserVaildate())->goCheck('edituserpic');
        (new UserModel())->editUserPic();
        return self::showResCodeWithOutData('修改头像成功');
    }

    /**
     * 修改用户资料
     * @return \think\response\Json
     * @throws \app\lib\exception\BaseException
     */
    public function editUserInfo()
    {
        (new UserVaildate())->goCheck('edituserinfo');
        (new UserModel())->editUserInfo();
        return self::showResCodeWithOutData('修改成功');
    }

    /**
     * 修改密码
     * @return \think\response\Json
     * @throws \app\lib\exception\BaseException
     */
    public function rePassword()
    {
        (new UserVaildate())->goCheck('repassword');
        (new UserModel())->rePassword();
        return self::showResCodeWithOutData('修改密码成功');
    }

    /**
     * 关注用户
     * @return \think\response\Json
     * @throws \app\lib\exception\BaseException
     */
    public function follow()
    {
        (new UserVaildate())->goCheck('follow');
        (new UserModel())->toFollow();
        return self::showResCodeWithOutData('关注成功');
    }

    /**
     * 取消关注
     * @return \think\response\Json
     * @throws \app\lib\exception\BaseException
     */
    public function unFollow()
    {
        (new UserVaildate())->goCheck('unfollow');
        (new UserModel())->toUnFollow();
        return self::showResCodeWithOutData('取消关注成功');
    }

    /**
     * 互关列表
     * @return \think\response\Json
     * @throws \app\lib\exception\BaseException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function friends()
    {
        (new UserVaildate())->goCheck('getfriends');
        $list = (new UserModel())->getFriendsList();
        return self::showResCode('获取成功', ['list' => $list]);
    }

    /**
     * 粉丝列表
     * @return \think\response\Json
     * @throws \app\lib\exception\BaseException
     */
    public function fans()
    {
        (new UserVaildate())->goCheck('getfans');
        $list = (new UserModel())->getFansList();
        return self::showResCode('获取成功', ['list' => $list]);
    }

    /**
     * 关注列表
     * @return \think\response\Json
     * @throws \app\lib\exception\BaseException
     */
    public function follows()
    {
        (new UserVaildate())->goCheck('getfollows');
        $list = (new UserModel())->getFollowsList();
        return self::showResCode('获取成功', ['list' => $list]);
    }
}
