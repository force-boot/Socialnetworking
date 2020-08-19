<?php
/*
Plugin Name: VAPTCHA 评论验证插件
Version: 1.0
Plugin URL:
Description: 有效防止刷评论，恶意提交，美化评论操作。
Author: XXX
Author URL: 无
*/

!defined('EMLOG_ROOT') && exit('access deined!');

function check(){

}

addAction('comment_post', 'check');