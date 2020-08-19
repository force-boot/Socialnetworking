<?php

namespace app\common\model;

use app\common\controller\ThirdLoginController;

use app\lib\exception\BaseException;

use think\App;
use think\Db;
use think\facade\Cache;

use app\common\controller\sms\Sms;

use think\Model;

/**
 * 用户主表
 * @package app\common\model
 * @author XieJiaWei<print_f@hotmail.com>
 * @version 1.0.0
 */
class User extends Model
{
    //自动写入时间戳
    protected $autoWriteTimestamp = true;

    /**
     * 绑定用户信息表
     * @return \think\model\relation\HasOne
     */
    public function userInfo(): Object
    {
        return $this->hasOne('Userinfo');
    }

    /**
     * 绑定第三方登录
     * @return Object
     */
    public function userBind(): Object
    {
        return $this->hasMany('UserBind');
    }

    /**
     * 发送验证码
     * @return mixed
     * @throws BaseException
     * @throws \AlibabaCloud\Client\Exception\ClientException
     */
    public function sendCode()
    {
        $phone = input('phone');
        $ip = request()->ip();
        //判断是否满足发送条件
        if (!$this->checkSendCode($phone, $ip)) ApiException('操作频繁', 30001, 200);
        $code = random_int(1000, 9999);
        //没有开启验证码功能
        if (!Sms::checkOpen()) {
            cache('sendCode_' . $phone, $code, Sms::getConfig('expire'));
            cache('sendCode_' . $ip) ? Cache::inc('sendCode_' . $ip) : cache('sendCode_' . $ip, 1, 86400);
            ApiException($code, 30002, 200);
        }
        //发送验证码
        $res = Sms::sendSms($phone, $code);
        //发送成功 写入缓存
        if ($res['Code'] == 'OK') {
            cache('sendCode_' . $ip) ? Cache::inc('sendCode_' . $ip) : cache('sendCode_' . $ip, 1, 86400);
            return cache('sendCode_' . $phone, $code, Sms::getConfig('expire'));
        }
        //发送失败
        ApiException($res['Message'], 30003, 200);
    }

    /**
     * 验证是否满足发送验证码条件
     * @param string $ip 请求ip
     * @param int $phone 手机号
     * @return bool
     */
    public function checkSendCode($phone, $ip): bool
    {
        //缓存是否失效
        if (cache('sendCode_' . $phone)) return false;
        //日ip限制条数
        if (cache('sendCode_' . $ip) >= Sms::getConfig('ipLimit')) return false;
        //满足条件
        return true;
    }

    /**
     * 判断用户是否存在
     * @param array $arr
     * @return array|bool|\PDOStatement|string|Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function isExists(array $arr = [])
    {
        if (array_key_exists('id', $arr)) { //用户ID
            return $this->where('id', $arr['id'])->find();
        }
        if (array_key_exists('phone', $arr)) { // 手机号
            $user = $this->where('phone', $arr['phone'])->find();
            if ($user) $user->logintype = 'phone';
            return $user;
        }
        if (array_key_exists('email', $arr)) { //邮箱
            $user = $this->where('email', $arr['email'])->find();
            if ($user) $user->logintype = 'email';
            return $user;
        }
        if (array_key_exists('username', $arr)) { //用户名
            $user = $this->where('username', $arr['username'])->find();
            if ($user) $user->logintype = 'username';
            return $user;
        }
        //第三方登录
        if (array_key_exists('provider', $arr)) {
            $where = [
                'type' => $arr['provider'],
                'openid' => $arr['openid']
            ];
            $user = $this->userBind()->where($where)->find();
            if ($user) $user->logintype = $arr['provider'];
            return $user;
        }
        return false;
    }

    /**
     * 手机号登录
     * @return string 返回token
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws BaseException
     */
    public function phoneLogin(): string
    {
        $param = input();
        //验证用户是否存在
        $user = $this->isExists(['phone' => $param['phone']]);
        //用户不存在，直接注册
        if (!$user) {
            // 用户主表
            $user = self::create([
                'username' => 'sns_' . $param['phone'],
                'phone' => $param['phone'],
                'ip' => request()->ip()
            ]);
            //在用户信息表创建对应的记录
            $user->userInfo()->create([
                'user_id' => $user->id,
                'city' => getIpCity()
            ]);
            $user->logintype = 'phone';
            return $this->createSaveToken($user->toArray());
        }
        //是否被禁用
        $this->checkStatus($user->toArray());
        //登录成功
        return $this->createSaveToken($user->toArray());
    }

    /**
     * 帐号密码登录
     * @return string
     * @throws BaseException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function login(): string
    {
        $param = input();
        // 验证用户是否存在
        $user = $this->isExists($this->filterUserData($param['username']));
        //用户不存在
        if (!$user) ApiException('登录账户不存在或未绑定', 20000, 200);
        // 是否被禁用
        $this->checkStatus($user->toArray(), true);
        //验证密码
        $this->checkPassword($param['password'], $user->password);

        //登录成功
        return $this->createSaveToken($user->toArray());
    }

    /**
     * 第三方登录 web and app
     * @return array|void
     * @throws BaseException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function thirdLogin()
    {
        //获取全部请求参数
        $param = input();
        //解密过程 （待添加）
        //web端生成登录链接
        if (isset($param['web']) && isset($param['create'])) return $this->createWebThirdLoginUrl($param['provider']);
        //web端登录回调
        if (isset($param['web']) && isset($param['callback'])) return $this->webThirdLoginCallBack($param);
        //APP端 直接解析传来的第三方登录参数
        return $this->parseThirdLoginParam($param);
    }

    /**
     * 解析第三方登录参数
     * @param array $param
     * @return array
     * @throws BaseException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function parseThirdLoginParam(array $param): array
    {
        //判断用户是否存在
        $user = $this->isExists([
            'provider' => $param['provider'],
            'openid' => $param['openid']
        ]);
        if (!$user) { //不存在创建
            $user = $this->userBind()->create([
                'type' => $param['provider'],
                'openid' => $param['openid'],
                'nickname' => $param['nickName'],
                'avatarurl' => $param['avatarUrl']
            ]);
            $arr = $user->toArray();
            $arr['expires_in'] = $param['expires_in'];
            $this->createSaveToken($arr);
        }
        //判断用户是否被禁用
        $arr = $this->checkStatus($user->toArray(), true);
        $arr['expires_in'] = $param['expires_in'];
        //登录成功
        return ['token' => $this->createSaveToken($arr)];
    }

    /**
     * 生成网页端第三方登录链接
     * @param string $type 登录方式
     * @return array
     */
    public function createWebThirdLoginUrl(string $type): array
    {
        return ['url' => (new ThirdLoginController($type))->getUrl()];
    }

    /**
     * 网页端第三方登录回调
     * @param array $param
     * @return array
     * @throws BaseException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function webThirdLoginCallBack(array $param)
    {
        $thirdLoginObj = new ThirdLoginController($param['provider']);
        //验证参数合法性
        $thirdLoginObj->checkParam($param);
        //获取第三方登录信息
        $userInfo = $thirdLoginObj->getUserInfo();
        $arr = [
            'provider' => $param['provider'],
            'openid' => $userInfo['openid'],
            'nickName' => $userInfo['nick'],
            'avatarUrl' => $userInfo['avatar'],
            'expires_in' => $thirdLoginObj->getToken()['expires_in']
        ];
        //判断用户是否为绑定操作
        if (isset($param['bind']) && $param['bind']) return $this->bindThird($arr);

        return $this->parseThirdLoginParam($arr);
    }

    /**
     * 验证密码
     * @param string|int $password
     * @param string $hash
     * @return bool
     * @throws BaseException
     */
    public function checkPassword($password, $hash): bool
    {
        if (!$hash) ApiException('密码错误', 20002, 200);
        // 密码错误
        if (!password_verify($password, $hash)) ApiException('密码错误', 20002, 200);

        return true;
    }

    /**
     * 验证用户名格式 手机号 or 昵称 or邮箱
     * @param string $data
     * @return array
     */
    public function filterUserData($data): array
    {
        $arr = [];
        //判断是否是手机号码
        if (preg_match('/^1[3-9][0-9]\d{8}$/', $data)) {
            $arr['phone'] = $data;
            return $arr;
        }
        //判断是否是邮箱
        if (preg_match('#[a-z0-9&\-_.]+@[\w\-_]+([\w\-.]+)?\.[\w\-]+#is', $data)) {
            $arr['email'] = $data;
            return $arr;
        }
        $arr['username'] = $data;
        return $arr;
    }

    /**
     * 验证账户状态
     * @param array $arr
     * @param bool $isReget
     * @return array
     * @throws BaseException
     */
    public function checkStatus(array $arr = [], bool $isReget = false): array
    {
        if ($isReget) {
            // 帐号密码登录 和第三方登录
            $userId = array_key_exists('user_id', $arr) ? $arr['user_id'] : $arr['id'];
            // 判断第三方登录是否完善了资料
            if ($userId < 1) return $arr;
            $user = $this->find($userId)->toArray();
            $status = $user['status'];
        } else {
            $status = $arr['status'];
        }
        if ($status == 0) ApiException('该账户已被禁用', 20001, 200);
        return $arr;
    }

    /**
     * 创建并保存Token
     * @param array $arr
     * @return string
     * @throws BaseException
     */
    public function createSaveToken(array $arr = []): string
    {
        // 生成token
        $token = createUniqueKey('token');
        $arr['token'] = $token;
        // 登录过期时间
        $expire = array_key_exists('expires_in', $arr) ? $arr['expires_in'] : config('api.token_expire');
        // 保存到缓存中
        if (!cache($token, $arr, $expire)) ApiException();
        //返回token
        return $token;
    }

    /**
     * 注销登录
     * @return bool
     * @throws BaseException
     */
    public function logout(): bool
    {
        if (!Cache::pull(request()->userToken)) ApiException('您已经退出了', 30004, 200);
        return true;
    }

    /**
     * 关联文章
     * @return \think\model\relation\HasMany
     */
    public function post(): object
    {
        return $this->hasMany('Post');
    }

    /**
     * 获取指定用户下文章
     * @return mixed
     * @throws BaseException
     */
    public function getPostList()
    {
        $params = input();
        $user = $this->get($params['id']);
        if (!$user) ApiException('用户不存在', 10000);
        return $user->post()->with([
            'user' => function ($query) {
                return $query->field('id,username,userpic');
            }, 'images' => function ($query) {
                return $query->field('url');
            }, 'share'])->where('isopen', 1)->page($params['page'], 10)->select();
    }

    /**
     * 获取当前登录用户下所有文章
     * @return mixed
     */
    public function getAllPostList()
    {
        $params = input();
        // 获取用户id
        $user_id = request()->userId;
        return $this->get($user_id)->post()->with([
            'user' => function ($query) {
                return $query->field('id,username,userpic');
            }, 'images' => function ($query) {
                return $query->field('url');
            }, 'share'])->page($params['page'], 10)->select();
    }

    /**
     * 搜索用户
     * @return array|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function search()
    {
        // 获取所有参数
        $param = input();
        return $this->where('username', 'like', '%' . $param['keyword'] . '%')
            ->page($param['page'], 10)
            ->hidden(['password'])
            ->select();
    }

    /**
     * 绑定类型验证
     * @param $current
     * @param $bindtype
     * @return bool
     * @throws BaseException
     */
    public function checkBindType($current, $bindType): bool
    {
        // 当前绑定类型
        if ($bindType == $current) ApiException('绑定类型冲突');
        return true;
    }

    /**
     * 获取验证绑定场景
     * @param $scene string 当前场景 phone or email
     * @return string
     */
    public function getCheckBindScene($scene)
    {
        return $scene == 'phone' ? 'mail' : 'phone';
    }

    /**
     * 解析用户绑定事件
     * @param $bindType string 绑定类型 目前只会是phone or email
     * @return bool
     * @throws BaseException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function parseUserBind($bindType)
    {
        // 获取所有参数
        $params = input();
        $currentUserInfo = request()->userTokenUserInfo;
        $currentUserId = request()->userId;
        // 当前登录类型
        $currentLoginType = $currentUserInfo['logintype'];
        // 验证绑定类型是否冲突
        $this->checkBindType($currentLoginType, $bindType);
        // 查询是否绑定了其他用户
        $bindUser = $this->isExists([$bindType => $params[$bindType]]);
        //获取验证场景
        $chekScene = $this->getCheckBindScene($currentLoginType);
        //当前登录类型表单数据
        $loginTypeData = $params[$bindType];
        // 存在
        if ($bindUser) {
            if ($currentLoginType == 'username' || $currentLoginType == $chekScene) ApiException('已被绑定', 20006, 200);
            // 第三方登录
            if ($bindUser->userBind()->where('type', $currentLoginType)->find()) ApiException('已被绑定', 20006, 200);
            // 直接修改
            $userBind = $this->userBind()->find($currentUserInfo['id']);
            $userBind->user_id = $bindUser->id;
            if ($userBind->save()) {
                // 更新缓存
                $currentUserInfo['user_id'] = $bindUser->id;
                Cache::set($currentUserInfo['token'], $currentUserInfo, $currentUserInfo['expires_in']);
                return true;
            }
            ApiException();
        }
        // 不存在
        // 账号邮箱登录
        if ($currentLoginType == 'username' || $currentLoginType == $chekScene) {
            $user = $this->save([
                $bindType => $loginTypeData
            ], ['id' => $currentUserId]);
            // 更新缓存
            $currentUserInfo[$bindType] = $loginTypeData;
            Cache::set($currentUserInfo['token'], $currentUserInfo, config('api.token_expire'));
            return true;
        }
        // 第三方登录
        if (!$currentUserId) {
            // 在user表创建账号
            $user = $this->create([
                'username' => $loginTypeData,
                $bindType => $loginTypeData,
            ]);
            // 绑定
            $userBind = $this->userBind()->find($currentUserInfo['id']);
            $userBind->user_id = $user->id;
            if ($userBind->save()) {
                // 更新缓存
                $currentUserInfo['user_id'] = $user->id;
                Cache::set($currentUserInfo['token'], $currentUserInfo, $currentUserInfo['expires_in']);
                return true;
            }
            ApiException();
        }
        // 直接修改
        if ($this->save([
            $bindType => $loginTypeData
        ], ['id' => $currentUserId])) return true;
        ApiException();
    }


    /**
     * 绑定手机
     * @return bool
     * @throws BaseException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public
    function bindPhone()
    {
        return $this->parseUserBind('phone');
    }

    /**
     * 绑定邮箱
     * @return bool
     * @throws BaseException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public
    function bindEmail()
    {
        return $this->parseUserBind('email');
    }


    /**
     * 绑定第三方
     * @param $params array
     * @return mixed
     * @throws BaseException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public
    function bindThird($params = [])
    {
        !empty($params) ?: $params = input();
        $currentUserInfo = request()->userTokenUserInfo;
        $currentUserId = request()->userId;
        // 当前登录类型
        $currentLoginType = $currentUserInfo['logintype'];
        // 验证绑定类型是否冲突
        $this->checkBindType($currentLoginType, $params['provider']);
        // 查询是否绑定了其他用户
        $bindUser = $this->isExists(['provider' => $params['provider'], 'openid' => $params['openid']]);
        // 存在
        if ($bindUser) {
            if ($bindUser->user_id) ApiException('已被绑定', 20006, 200);
            $bindUser->user_id = $currentUserId;
            return $bindUser->save();
        }
        // 不存在
        return $this->userBind()->create([
            'type' => $params['provider'],
            'openid' => $params['openid'],
            'nickname' => $params['nickName'],
            'avatarurl' => $params['avatarUrl'],
            'user_id' => $currentUserId
        ]);
    }

    /**
     * 修改头像
     * @return bool
     * @throws BaseException
     */
    public function editUserPic()
    {
        // 获取用户id
        $userid = request()->userId;
        $image = (new Image())->upload($userid, 'userpic');
        // 修改用户头像
        $user = self::get($userid);
        $user->userpic = $image->url;
        if ($user->save()) return true;
        ApiException();
    }

    /**
     * 修改用户资料
     * @return bool
     */
    public function editUserInfo()
    {
        // 获取所有参数
        $params = input();
        // 获取用户id
        $userid = request()->userId;
        // 修改昵称
        $user = $this->get($userid);
        $user->username = $params['name'];
        $user->save();
        // 修改用户信息表
        $userInfo = $user->userinfo()->find();
        $userInfo->sex = $params['sex'];
        $userInfo->age = $params['age'];
        // 是否设置个性签名
        if (isset($params['sign'])) $userInfo->sign = $params['sign'];
        $userInfo->birthday = $params['birthday'];
        $userInfo->city = $params['city'];
        $userInfo->save();
        return true;
    }


    /**
     * 修改密码
     * @throws BaseException
     */
    public function repassword()
    {
        // 获取所有参数
        $params = input();
        // 获取用户id
        $userid = request()->userId;
        $user = self::get($userid);
        // 手机注册的用户并没有原密码,直接修改即可
        if ($user['password']) {
            // 判断旧密码是否正确
            $this->checkPassword($params['oldpassword'], $user['password']);
        }
        // 修改密码
        $newPassword = password_hash($params['newpassword'], PASSWORD_DEFAULT);
        $res = $this->save([
            'password' => $newPassword
        ], ['id' => $userid]);
        if (!$res) ApiException('修改密码失败', 20009, 200);
        $user['password'] = $newPassword;
        // 更新缓存信息
        Cache::set(request()->Token, $user, config('api.token_expire'));
    }

    /**
     * 关联关注
     * @return \think\model\relation\HasMany
     */
    public function withfollow()
    {
        return $this->hasMany('Follow', 'user_id');
    }

    /**
     * 关注用户
     * @return bool
     * @throws BaseException
     */
    public function toFollow()
    {
        // 获取所有参数
        $params = input();
        // 获取用户id
        $user_id = request()->userId;
        $follow_id = $params['follow_id'];
        // 不能关注自己
        if ($user_id == $follow_id) ApiException('非法操作', 10000, 200);
        // 获取到当前用户的关注模型
        $followModel = $this->get($user_id)->withfollow();
        // 查询记录是否存在
        $follow = $followModel->where('follow_id', $follow_id)->find();
        if ($follow) ApiException('你已经关注过了', 10000, 200);
        $followModel->create([
            'user_id' => $user_id,
            'follow_id' => $follow_id
        ]);
        return true;
    }

    /**
     * 取消关注
     * @throws BaseException
     */
    public function toUnFollow()
    {
        // 获取所有参数
        $params = input();
        // 获取用户id
        $user_id = request()->userId;
        $follow_id = $params['follow_id'];
        // 不能取消关注自己
        if ($user_id == $follow_id) ApiException('非法操作', 10000, 200);
        $followModel = $this->get($user_id)->withfollow();
        $follow = $followModel->where('follow_id', $follow_id)->find();
        if (!$follow) ApiException('暂未关注', 10000, 200);
        $follow->delete();
    }

    /**
     * 获取互关列表
     * @return array|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getFriendsList()
    {
        // 获取所有参数
        $params = request()->param();
        // 获取用户id
        $userid = request()->userId;
        $page = $params['page'];
        $follows = $this->where('id', 'IN', function ($query) use ($userid) {
            $query->table('follow')
                ->where('user_id', 'IN', function ($query) use ($userid) {
                    $query->table('follow')->where('user_id', $userid)->field('follow_id');
                })->where('follow_id', $userid)
                ->field('user_id');
        })->field('id,username,userpic')->page($page, 10)->select();
        return $follows;
    }

    /**
     * 关联粉丝列表
     * @return \think\model\relation\BelongsToMany
     */
    public function fans()
    {
        return $this->belongsToMany('User', 'Follow', 'user_id', 'follow_id');
    }

    /**
     * 获取当前用户粉丝列表
     * @return array
     */
    public function getFansList()
    {
        // 获取所有参数
        $params = input();
        // 获取用户id
        $userid = request()->userId;
        $fens = $this->get($userid)->fans()->page($params['page'], 10)->select()->toArray();
        return $this->filterReturn($fens);
    }

    /**
     * 关联关注列表
     * @return \think\model\relation\BelongsToMany
     */
    public function follows()
    {
        return $this->belongsToMany('User', 'Follow', 'follow_id', 'user_id');
    }

    /**
     * 获取当前用户关注列表
     * @return array
     */
    public function getFollowsList()
    {
        // 获取所有参数
        $params = input();
        // 获取用户id
        $userid = request()->userId;
        $follows = $this->get($userid)->follows()->page($params['page'], 10)->select()->toArray();
        return $this->filterReturn($follows);
    }

    /**
     * 关注和粉丝返回字段
     * @param array $param
     * @return array
     */
    public function filterReturn($param = [])
    {
        $arr = [];
        $length = count($param);
        for ($i = 0; $i < $length; $i++) {
            $arr[] = [
                'id' => $param[$i]['id'],
                'username' => $param[$i]['username'],
                'userpic' => $param[$i]['userpic'],
            ];
        }
        return $arr;
    }
}
