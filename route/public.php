<?php
/**
 * 不需要验证token
 */

Route::group('api/:version/', function () {
    //发送验证码
    Route::post('/user/sendcode', 'api/:version.User/sendCode');
    //手机号登录
    Route::post('/user/phonelogin', 'api/:version.User/phoneLogin');
    //帐号密码登录
    Route::post('/user/login', 'api/:version.User/Login');
    //第三方登录
    Route::post('/user/thirdlogin', 'api/:version.User/thirdLogin');
    //获取文章分类
    Route::get('/postclass', 'api/:version.PostClass/index');
    //获取话题分类
    Route::get('/topicclass', 'api/:version.TopicClass/index');
    //获取热门话题
    Route::get('/hottopic', 'api/:version.Topic/index');
    //获取指定话题分类下的话题列表
    Route::get('/topicclass/:id/topic/:page', 'api/:version.TopicClass/topic');
    //获取文章详情
    Route::get('/post/:id', 'api/:version.Post/index');
    //获取指定话题下的文章列表
    Route::get('/topic/:id/post/:page', 'api/:version.Topic/post')->middleware(['ApiGetUserId']);
    //获取指定文章分类下的文章
    Route::get('/postclass/:id/post/:page', 'api/:version.PostClass/post')->middleware(['ApiGetUserId']);
    //获取指定用户下的文章
    Route::get('/user/:id/post/:page', 'api/:version.User/post');
    //搜索话题
    Route::post('/search/topic', 'api/:version.Search/topic');
    //搜索文章
    Route::post('/search/post', 'api/:version.Search/post');
    //搜索用户
    Route::post('/search/user', 'api/:version.Search/user');
    //综合搜索
    Route::post('/search/think', 'api/:version.Search/keywordThink');
    //热搜榜单
    Route::post('/search/hot', 'api/:version.Search/hotRank');
    //广告列表
    Route::get('/adsense/:type', 'api/:version.AdSense/index');
    //获取当前文章的所有评论
    Route::get('/post/:id/comment', 'api/:version.Post/comment');
    //检测更新
    Route::post('/update', 'api/:version.Update/index');
});