<?php

declare(strict_types=1);
/**
 * This file is part of Kuaijing Bailing.
 *
 * @link     https://www.kuaijingai.com
 * @document https://help.kuaijingai.com
 * @contact  www.kuaijingai.com 7*12 9:00-21:00
 */
namespace Bailing\Helper;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Filesystem\FilesystemFactory;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemException;

/**
 * Class UploadHelper.
 */
class UploadHelper
{
    #[Inject]
    private ConfigInterface $config;

    #[Inject]
    private FilesystemFactory $filesystemFactory;

    /**
     * 文件系统选择.
     */
    private string $filesystemType;

    public function __construct()
    {
        $this->filesystemType = cfg('filesystem_type');
        if (! $this->filesystemType) {
            $this->filesystemType = 'cos';
        }

        $this->config->set('file.default', $this->filesystemType);
        stdLog()->debug('file.default：' . $this->config->get('file.default'));

        if ($this->filesystemType == 'cos') {
            // 重新从数据库读 cos 配置项
            $this->config->set('file.storage.cos.region', cfg('filesystem_qcloud_region'));
            $this->config->set('file.storage.cos.app_id', cfg('qcloud_appid'));
            $this->config->set('file.storage.cos.secret_id', cfg('qcloud_secret_id'));
            $this->config->set('file.storage.cos.secret_key', cfg('qcloud_secret_key'));
            $this->config->set('file.storage.cos.bucket', cfg('filesystem_qcloud_bucket'));
        }
        if ($this->filesystemType == 'minio' || $this->filesystemType == 's3') {
            // 重新从数据库读 s3 配置项
            $this->config->set('file.storage.' . $this->filesystemType . '.credentials.key', cfg('filesystem_s3_key'));
            $this->config->set('file.storage.' . $this->filesystemType . '.credentials.secret', cfg('filesystem_s3_secret'));
            $this->config->set('file.storage.' . $this->filesystemType . '.region', cfg('filesystem_s3_region'));
            $this->config->set('file.storage.' . $this->filesystemType . '.endpoint', cfg('filesystem_s3_endpoint'));
            $this->config->set('file.storage.' . $this->filesystemType . '.bucket_name', cfg('filesystem_s3_bucket'));
        }
        if ($this->filesystemType == 'oss') {
            // 重新从数据库读 oss 配置项
            $this->config->set('file.storage.' . $this->filesystemType . '.accessId', cfg('filesystem_oss_access_id'));
            $this->config->set('file.storage.' . $this->filesystemType . '.accessSecret', cfg('filesystem_oss_access_secret'));
            $this->config->set('file.storage.' . $this->filesystemType . '.bucket', cfg('filesystem_oss_bucket'));
            $this->config->set('file.storage.' . $this->filesystemType . '.endpoint', cfg('filesystem_oss_endpoint'));
        }
        if ($this->filesystemType == 'ftp') {
            // 重新从数据库读 ftp 配置项
            $this->config->set('file.storage.' . $this->filesystemType . '.host', cfg('filesystem_ftp_host'));
            $this->config->set('file.storage.' . $this->filesystemType . '.port', (int) cfg('filesystem_ftp_port'));
            $this->config->set('file.storage.' . $this->filesystemType . '.username', cfg('filesystem_ftp_username'));
            $this->config->set('file.storage.' . $this->filesystemType . '.password', cfg('filesystem_ftp_password'));
            $this->config->set('file.storage.' . $this->filesystemType . '.root', cfg('filesystem_ftp_root'));
            $this->config->set('file.storage.' . $this->filesystemType . '.ssl', (bool) cfg('filesystem_ftp_ssl'));
        }
    }

    /**
     * 上传文件.
     * @throws FilesystemException
     * @throws Exception
     */
    public function uploadFile(mixed $file, string $fileDir): array
    {
        if (! $fileDir) {
            $fileDir = 'files';
        }

        if (! $file) {
            throw new \Exception('请上传文件');
        }

        $allowExtension = ['png', 'gif', 'jpeg', 'jpg', 'zip', 'txt', 'xls', 'xlsx', 'doc', 'docs', 'pdf', 'rar'];
        if (! in_array($file->getExtension(), $allowExtension)) {
            throw new \Exception('文件格式（' . $file->getExtension() . '）不允许，只允许 ' . implode('，', $allowExtension));
        }

        /*
         * 文件Mime暂不校验
         *
        $allowMimeType = ['image/png', 'image/gif', 'image/jpeg', 'application/zip', 'text/plain', 'application/vnd.ms-excel', 'application/msword'];
        if (! in_array($file->getMimeType(), $allowMimeType)) {
            return ApiHelper::genErrorData('图片mime类型（' . $file->getMimeType() . '）不允许，只允许 ' . implode('，', $allowMimeType));
        }
        */

        $stream = fopen($file->getRealPath(), 'r+');

        $fileName = 'upload/' . $fileDir . '/' . date('Ymd') . '/' . uniqid() . mt_rand(10000, 99999) . '.' . $file->getExtension();
        $this->filesystemFactory->get($this->filesystemType)->writeStream(
            $fileName,
            $stream
        );
        if (is_resource($stream)) {
            fclose($stream);
        }

        //为了后续纠出传不合法文件人的信息，记入日志。
        logger()->info('uploadFile' . $fileName, request()->getHeaders());

        return ['fileName' => $fileName, 'fileUrl' => fileDomain($fileName)];
    }

    /**
     * 上传图片.
     * @param mixed $fileDir
     * @throws FilesystemException
     * @throws Exception
     */
    public function uploadImage(mixed $file, string $fileDir): array
    {
        if (! $fileDir) {
            $fileDir = 'images';
        }

        if (! $file) {
            return ApiHelper::genErrorData('请上传文件');
        }

        $allowExtension = ['png', 'gif', 'jpeg', 'jpg'];
        if (! in_array($file->getExtension(), $allowExtension)) {
            throw new \Exception('图片格式（' . $file->getExtension() . '）不允许，只允许 ' . implode('，', $allowExtension));
        }

        $allowMimeType = ['image/png', 'image/gif', 'image/jpeg'];
        if (! in_array($file->getMimeType(), $allowMimeType)) {
            throw new \Exception('图片mime类型（' . $file->getMimeType() . '）不允许，只允许 ' . implode('，', $allowMimeType));
        }

        $stream = fopen($file->getRealPath(), 'r+');

        $fileName = 'upload/' . $fileDir . '/' . date('Ymd') . '/' . uniqid() . mt_rand(10000, 99999) . '.' . $file->getExtension();
        $this->filesystemFactory->get($this->filesystemType)->writeStream(
            $fileName,
            $stream
        );
        if (is_resource($stream)) {
            fclose($stream);
        }

        //为了后续纠出传不合法文件人的信息，记入日志。
        logger()->info('uploadImage' . $fileName);

        return ['fileName' => $fileName, 'fileUrl' => fileDomain($fileName)];
    }

    /**
     * 上传远程文件(服务应用内部调用，先下载再上传).
     * @param string $fileUrl 远程文件网址，可get，且状态码200
     * @param string $folder 文件目录
     * @throws FilesystemException
     * @throws GuzzleException
     * @throws Exception
     */
    public function uploadRemoteFile(string $fileUrl, string $extension = '', string $folder = 'remote'): array
    {
        $clientHttp = new Client();
        $response = $clientHttp->get($fileUrl);
        $body = $response->getBody();

        if ($response->getStatusCode() != 200) {
            throw new Exception(sprintf('文件下载失败（错误码%s）', $response->getStatusCode()));
        }

        //获取响应体，对象
        $fileStr = (string) $body;

        //没有指定上传保存扩展名，通过链接获取
        if (! $extension) {
            $extension = pathinfo($fileUrl, PATHINFO_EXTENSION);
        }

        $uploadFile = 'upload/' . $folder . '/' . date('Ymd') . '/' . uniqid() . mt_rand(10000, 99999) . '.' . $extension;
        $this->filesystemFactory->get($this->filesystemType)->write($uploadFile, $fileStr);

        return ['fileName' => $uploadFile, 'fileUrl' => fileDomain($uploadFile)];
    }

    /**
     * 上传本地文件(服务应用内部调用).
     * @param mixed $file /opt/www/runtime/doc/1640071827.docx
     * @param string $folder 文件目录
     * @throws FilesystemException
     */
    public function uploadLocalFile($file, string $folder = 'contract', bool $unlink = false): array  //线上开启
    {
        if (! file_exists($file)) {
            return ApiHelper::genErrorData('文件不存在');
        }
        $extension = pathinfo($file, PATHINFO_EXTENSION);
        $uploadFile = 'upload/' . $folder . '/' . date('Ymd') . '/' . uniqid() . mt_rand(10000, 99999) . '.' . $extension;
        // Add local file
        $stream = fopen($file, 'r+'); //fopen得打开本地决对路径
        $this->filesystemFactory->get($this->filesystemType)->writeStream($uploadFile, $stream); // null  上传成功
        if (is_resource($stream)) {
            fclose($stream);
        }
        $unlink && unlink($file);
        return ApiHelper::genSuccessData(['fileName' => $uploadFile, 'fileUrl' => fileDomain($uploadFile)]);
    }

    /**
     * 上传本地文件(服务应用内部调用).
     * @param mixed $file doc/1640071827.docx
     * @param string $folder 文件目录
     * @throws FilesystemException
     * @throws Exception
     */
    public function uploadLocalFilesystem(string $file, string $folder = 'contract', bool $unlink = false): array
    {
        $localFile = $this->filesystemFactory->get('local')->fileExists($file);
        if (! $localFile) {
            throw new \Exception('文件不存在');
        }
        $extension = pathinfo($file, PATHINFO_EXTENSION);
        $uploadFile = 'upload/' . $folder . '/' . date('Ymd') . '/' . uniqid() . mt_rand(10000, 99999) . '.' . $extension;
        // Add filesystem local file
        $localStream = $this->filesystemFactory->get('local')->read($file); //不含下载目录的filesystem local文件路径 二进制流
        $this->filesystemFactory->get($this->filesystemType)->write($uploadFile, $localStream); //相应判断文件应fileExists  null 写入成功
        $unlink ? $this->filesystemFactory->get('local')->delete($file) : false;
        return ['fileName' => $uploadFile, 'fileUrl' => fileDomain($uploadFile)];
    }

    /**
     * 下载腾讯云文件至本地(服务应用内部调用).
     * @throws FilesystemException
     * @throws Exception
     */
    public function downLoadFile(string $file, string $folder = 'contract', string $fileName = ''): array
    {
        $file = relativePath($file);
        $cosFileIsset = $this->filesystemFactory->get($this->filesystemType)->readStream($file);
        if ($cosFileIsset) {
            $cosStream = $this->filesystemFactory->get($this->filesystemType)->read($file); //二进制流
            if (! $fileName) {
                $extension = pathinfo($file, PATHINFO_EXTENSION);
                $fileName = uniqid() . mt_rand(10000, 99999) . '.' . $extension;
            }
            $localFile = 'upload/' . $folder . '/' . date('Ymd') . '/' . $fileName;
            $this->filesystemFactory->get('local')->write($localFile, $cosStream);
            $downLoadFile = $this->filesystemFactory->get('local')->fileExists($localFile);
            if ($downLoadFile) {
                return ['fileName' => $localFile, 'fileUrl' => 'runtime/' . $localFile];
            }
        }
        throw new Exception('操作异常');
    }
}
