<?php

namespace app\common\controller;

use app\common\controller\store\Store;
use think\File;

/**
 * 文件上传类
 * @package app\common\controller
 * @author XieJiaWei<print_f@hotmail.com>
 * @version 1.0.0
 */
class FileController
{
    /**
     * 默认配置参数
     * @var array
     */
    public $config = [
        'size' => 2067800,
        'ext' => 'jpg,png,gif,mp4',
        'path' => 'uploads'
    ];

    /**
     * 存储file对象
     * @var File
     */
    public $file;

    /**
     * FileController constructor.
     * @param array $option
     */
    public function __construct($option = [])
    {
        if (!empty($option)) $this->config = array_merge($this->config, $option);
    }

    /**
     * 上传文件处理
     * @param $files
     * @return array
     */
    public function UploadHandle($files): array
    {
        // 保存file对象
        $this->file = $files->validate($this->config);
        // 判断存储方式
        $storeType = config('api.store.type');
        if ($storeType != '' && $storeType != 'local') return $this->remoteStore();
        return $this->localStore();
    }

    /**
     * 本地存储
     * @return array
     */
    private function localStore(): array
    {
        //保存到本地文件夹
        $res = $this->file->move($this->config['path']);
        $filePath = $res->getPathname();
        return [
            'data' => $res ? getFileUrl($filePath) : $res->getError(),
            'status' => $res ? true : false,
            'image' => $this->getImageInfo($filePath)
        ];
    }


    /**
     * 远程存储
     * @return array
     */
    public function remoteStore(): array
    {
        //保存到系统临时目录
        $info = $this->file->move(sys_get_temp_dir());
        $filePath = $info->getPathname();
        $res = Store::upload($info->getFilename(), $filePath);
        return [
            'data' => $res,
            'status' => $res ? true : false,
            'image' => $this->getImageInfo($filePath)
        ];
    }

    /**
     * 获取图片文件信息
     * @param string $filePath
     * @return array
     */
    private function getImageInfo($filePath): array
    {
        $info = getimagesize($filePath);
        if (!is_array($info)) return [];
        $arr = [
            'width' => $info[0],
            'height' => $info[1],
            'ext' => pathinfo($filePath, PATHINFO_EXTENSION)
        ];
        return $arr;
    }

    /**
     * 析构方法
     */
    public
    function __destruct()
    {
        $this->file = '';
    }
}
