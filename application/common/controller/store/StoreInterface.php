<?php


namespace app\common\controller\store;

/**
 * Interface StoreInterface
 * @package app\common\controller\store
 */
interface StoreInterface
{
    /**
     * 上传文件
     * @param $fileName
     * @param $filePath
     * @return mixed
     */
    public function upload($fileName, $filePath);

    /**
     * 删除单个文件
     * @param string $file
     * @return mixed
     */
    public function deleteOne(string $file);

    /**
     * 批量删除文件
     * @param array $files
     * @return mixed
     */
    public function deleteMany(array $files);
}