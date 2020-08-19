<?php
// 只需要验证token
Route::group('api/:version/', function () {
    //退出登录
    Route::post('/user/logout', 'api/:version.User/logout');
    //绑定手机
    Route::post('/user/bindphone', 'api/:version.User/bindPhone');
})->middleware(['ApiUserAuth']);

// 用户操作（需完善资料和验证token）
Route::group('api/:version/', function () {
    // 上传多图
    Route::post('/image/uploadmore', 'api/:version.Image/uploadMore');
    // 发布文章
    Route::post('/post/create', 'api/:version.Post/create');
    // 获取当前用户下的所有文章（含隐私）
    Route::get('/user/post/:page', 'api/:version.User/allPost');
    // 绑定邮箱
    Route::post('/user/bindemail', 'api/:version.User/bindEmail');
    // 绑定第三方
    Route::post('/user/bindthird', 'api/:version.User/bindThird');
    // 用户点赞
    Route::post('/support', 'api/:version.Support/index');
    // 用户评论
    Route::post('/post/comment', 'api/:version.Comment/create');
    // 编辑头像
    Route::post('/edituserpic', 'api/:version.User/editUserPic');
    // 编辑资料
    Route::post('/edituserinfo', 'api/:version.User/editUserInfo');
    // 修改密码
    Route::post('/repassword', 'api/:version.User/rePassword');
    // 加入黑名单
    Route::post('/addblack', 'api/:version.Blacklist/addBlack');
    // 移出黑名单
    Route::post('/removeblack', 'api/:version.Blacklist/removeBlack');
    // 关注用户
    Route::post('/follow', 'api/:version.User/follow');
    // 取消关注
    Route::post('/unfollow', 'api/:version.User/unFollow');
    // 互关列表
    Route::get('/friends/:page', 'api/:version.User/friends');
    // 粉丝列表
    Route::get('/fans/:page', 'api/:version.User/fans');
    // 关注列表
    Route::get('/follows/:page', 'api/:version.User/follows');
    // 用户反馈
    Route::post('/feedback', 'api/:version.Feedback/create');
    // 获取用户反馈列表
    Route::get('/feedbacklist/:page', 'api/:version.Feedback/index');
})->middleware(['ApiUserAuth', 'ApiUserBind', 'ApiUserStatus']);

// socket 部分
Route::group('api/:version/', function () {
    // 发送信息
    Route::post('/chat/send', 'api/:version.Chat/send');
    // 获取未接受信息
    Route::post('/chat/get', 'api/:version.Chat/get');
    // 绑定上线
    Route::post('/chat/bind', 'api/:version.Chat/bind');
})->middleware(['ApiUserAuth', 'ApiUserBindPhone', 'ApiUserStatus']);