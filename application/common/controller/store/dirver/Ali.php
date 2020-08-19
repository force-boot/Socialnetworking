<?php


namespace app\common\controller\store\dirver;

use app\common\controller\store\StoreInterface;
use OSS\OssClient;
use OSS\Core\OssException;

/**
 * 阿里OSS
 * @package app\common\controller\store\dirver
 * @author XieJiaWei<print_f@hotmail.com>
 * @version 1.0.0
 */
class Ali implements StoreInterface
{
    /**
     * @var OssClient 存储对象
     */
    private $oss;

    /**
     * 配置参数
     * @var array
     */
    private $config = [
        'accessKeyId' => '',
        'accessSecret' => '',
        'endPoint' => '', //EndPoint（地域节点）
        'bucket' => '', //存储桶名称
    ];

    /**
     * Ali constructor.
     * @param $option
     * @throws OssException
     */
    public function __construct($option)
    {
        $this->config = array_merge($this->config, $option);
        $this->oss = new OssClient($this->config['accessKeyId'], $this->config['accessSecret'], $this->config['endPoint']);
    }

    /**
     * 删除单个文件
     * @param $file string 文件名
     * @return mixed
     */
    public function deleteOne(string $file)
    {
        return $this->oss->deleteObject($this->config['bucket'], $file);
    }

    /**
     * 批量删除文件
     * @param array $files
     * @return \OSS\Http\ResponseCore
     */
    public function deleteMany(array $files = [])
    {
        return $this->oss->deleteObjects($this->config['bucket'], $files);
    }

    /**
     * 上传文件
     * @param $fileName string 文件名
     * @param $filePath string 本地文件路径
     * @return string|bool bucket 文件路径
     * @throws \app\lib\exception\BaseException
     */
    public function upload($fileName, $filePath)
    {
        try {
            $res = $this->oss->uploadFile($this->config['bucket'], $fileName, $filePath);
            if ($res && unlink($filePath)) return $res['info']['url'];
            return false;
        } catch (OssException $e) {
            ApiException($e->getMessage(), 10001, 200);
        }
    }
}