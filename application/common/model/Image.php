<?php

namespace app\common\model;

use app\common\controller\FileController;
use think\Model;

/**
 * 图片
 * @package app\common\model
 * @author XieJiaWei<print_f@hotmail.com>
 * @version 1.0.0
 */
class Image extends Model
{
    //自动写入时间戳
    protected $autoWriteTimestamp = true;

    /**
     * 上传多图
     * @return Image|\think\Collection
     * @throws \app\lib\exception\BaseException
     */
    public function uploadMore()
    {
        return $this->upload(request()->userId, 'imglist');
    }

    /**
     * 上传文件
     * @param string $userid
     * @param string $field
     * @return Image|\think\Collection
     * @throws \app\lib\exception\BaseException
     */
    public function upload($userid = '', $field = '')
    {
        // 获取图片
        $files = request()->file($field);
        if (is_array($files)) {
            // 多图上传
            $arr = [];
            foreach ($files as $file) {
                $res = (new FileController)->UploadHandle($file);
                if ($res['status']) {
                    $arr[] = [
                        'url' => $res['data'],
                        'width' => $file['image']['width'],
                        'height' => $file['image']['height'],
                        'user_id' => $userid
                    ];
                }
            }
            return $this->saveAll($arr);
        }
        // 单图上传
        if (!$files) ApiException('请选择要上传的图片', 10000, 200);
        // 单文件上传
        $file = (new FileController)->UploadHandle($files);
        // 上传失败
        if (!$file['status']) ApiException($file['data'], 10000, 200);
        // 上传成功，写入数据库
        return self::create([
            'url' => $file['data'],
            'width' => $file['image']['width'],
            'height' => $file['image']['height'],
            'user_id' => $userid
        ]);
    }

    /**
     * 图片是否存在
     * @param $id
     * @param $userid
     * @return array|\PDOStatement|string|Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function isImageExist($id, $userid)
    {
        return $this->where('user_id', $userid)
            ->field('id')
            ->find($id);
    }
}
