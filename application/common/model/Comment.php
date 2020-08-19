<?php

namespace app\common\model;

use think\Model;

/**
 * 用户评论
 * @package app\common\model
 * @author XieJiaWei<print_f@hotmail.com>
 * @version 1.0.0
 */
class Comment extends Model
{
    //自动写入时间戳
    protected $autoWriteTimestamp = true;

    /**
     * 关联用户
     * @return mixed
     */
    public function user()
    {
        return $this->belongsTo('User', 'user_id');
    }

    /**
     * 用户评论
     * @return bool|Model
     * @throws \app\lib\exception\BaseException
     */
    public function comment()
    {
        $params = input();
        // 获得当前用户id
        $userid = request()->userId;
        $comment = $this->create([
            'user_id' => $userid,
            'post_id' => $params['post_id'],
            'fid' => $params['fid'],
            'data' => $params['data']
        ]);
        // 评论成功
        if ($comment) {
            if ($params['fid'] > 0) {
                $fcomment = self::get($params['fid']);
                $fcomment->fnum = ['inc', 1];
                $fcomment->save();
            }
            return true;
        }
        ApiException('评论失败');
    }
}
