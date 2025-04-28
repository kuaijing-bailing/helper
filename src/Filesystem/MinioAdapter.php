<?php

declare(strict_types=1);
/**
 * This file is part of Kuaijing Bailing.
 *
 * @link     https://www.kuaijingai.com
 * @document https://help.kuaijingai.com
 * @contact  www.kuaijingai.com 7*12 9:00-21:00
 */
namespace Bailing\Filesystem;

use Bailing\Filesystem\Minio\Minio;
use League\Flysystem\Config;
use League\Flysystem\DirectoryAttributes;
use League\Flysystem\FileAttributes;
use League\Flysystem\FilesystemAdapter;
use League\Flysystem\PathPrefixer;
use League\Flysystem\UnableToCopyFile;
use League\Flysystem\UnableToDeleteDirectory;
use League\Flysystem\UnableToDeleteFile;
use League\Flysystem\UnableToReadFile;
use League\Flysystem\UnableToRetrieveMetadata;
use League\Flysystem\UnableToWriteFile;
use League\Flysystem\Visibility;
use Overtrue\CosClient\BucketClient;
use Overtrue\CosClient\Exceptions\ClientException;
use Overtrue\CosClient\ObjectClient;

class MinioAdapter implements FilesystemAdapter
{
    protected ?ObjectClient $objectClient;

    protected ?BucketClient $bucketClient;

    protected PathPrefixer $prefixer;

    protected Minio $minio;

    protected array $config;

    public function __construct(array $config)
    {
        //检测endpoint是否正确
        $url = parse_url($config['endpoint']);
        if (empty($url['host'])) {
            throw UnableToReadFile::fromLocation($config['endpoint'], 'ENDPOINT错误，需要是http[s]://开头的网址');
        }

        $this->config = $config;

        $this->minio = new Minio($this->config);

        $this->prefixer = new PathPrefixer($config['prefix'] ?? '', DIRECTORY_SEPARATOR);
    }

    public function fileExists(string $path): bool
    {
        $prefixedPath = $this->prefixer->prefixPath($path);

        $response = $this->minio->getObjectInfo($prefixedPath);

        return $response['code'] == 200;
    }

    public function directoryExists(string $path): bool
    {
        return $this->fileExists($path);
    }

    public function write(string $path, string $contents, Config $config): void
    {
        $prefixedPath = $this->prefixer->prefixPath($path);

        // 先将文件存储到本地临时目录
        $tmpFile = tmpDir() . uniqid() . mt_rand(10000, 99999);
        file_put_contents($tmpFile, $contents);

        //上传到minio
        $response = $this->minio->putObject($tmpFile, $prefixedPath);

        // 删除临时文件
        unlink($tmpFile);

        if ($response['code'] != 200) {
            stdLog()->error('minio write file error：' . ($response['error']['Message'] ?? '写入失败'), ['tmpFile' => $tmpFile, 'prefixedPath' => $prefixedPath, 'response' => $response]);
            throw UnableToWriteFile::atLocation($path, (string) ($response['error']['Message'] ?? '写入失败'));
        }
    }

    public function writeStream(string $path, $contents, Config $config): void
    {
        $this->write($path, \stream_get_contents($contents), $config);
    }

    public function read(string $path): string
    {
        $prefixedPath = $this->prefixer->prefixPath($path);

        $response = $this->minio->getObject($prefixedPath);

        if ($response['code'] != 200) {
            stdLog()->error('minio read file error：' . ($response['error']['Message'] ?? '读取失败'), ['response' => $response]);
            throw UnableToReadFile::fromLocation($path, (string) ($response['error']['Message'] ?? '读取失败'));
        }

        return (string) $response['data'];
    }

    public function readStream(string $path)
    {
        $prefixedPath = $this->prefixer->prefixPath($path);

        $response = $this->minio->getObject($prefixedPath);

        if ($response['code'] != 200) {
            return false;
        }

        // 先将文件存储到本地临时目录
        $tmpFile = tmpDir() . uniqid() . mt_rand(10000, 99999);
        file_put_contents($tmpFile, $response['data']);

        return fopen($tmpFile, 'r+');
    }

    public function delete(string $path): void
    {
        $prefixedPath = $this->prefixer->prefixPath($path);

        $response = $this->minio->deleteObject($prefixedPath);

        if (! $response) {
            throw UnableToDeleteFile::atLocation($path, '删除文件失败');
        }
    }

    public function deleteDirectory(string $path): void
    {
        $dirname = $this->prefixer->prefixPath($path);

        $response = $this->listObjects($dirname);

        if (empty($response['Contents'])) {
            return;
        }

        $keys = array_map(
            function ($item) {
                return ['Key' => $item['Key']];
            },
            $response['Contents']
        );

        $response = $this->getObjectClient()->deleteObjects(
            [
                'Delete' => [
                    'Quiet' => 'false',
                    'Object' => $keys,
                ],
            ]
        );

        if (! $response->isSuccessful()) {
            throw UnableToDeleteDirectory::atLocation($path, (string) $response->getBody());
        }
    }

    public function createDirectory(string $path, Config $config): void
    {
        $dirname = $this->prefixer->prefixPath($path);

        $this->getObjectClient()->putObject($dirname . '/', '');
    }

    public function visibility(string $path): FileAttributes
    {
        $prefixedPath = $this->prefixer->prefixPath($path);

        $meta = $this->getObjectClient()->getObjectACL($prefixedPath);

        foreach ($meta['AccessControlPolicy']['AccessControlList']['Grant'] ?? [] as $grant) {
            if ($grant['Permission'] === 'READ' && str_contains($grant['Grantee']['URI'] ?? '', 'global/AllUsers')) {
                return new FileAttributes($path, null, Visibility::PUBLIC);
            }
        }

        return new FileAttributes($path, null, Visibility::PRIVATE);
    }

    public function mimeType(string $path): FileAttributes
    {
        $meta = $this->getMetadata($path);
        if (! $meta || $meta->mimeType() === null) {
            throw UnableToRetrieveMetadata::mimeType($path);
        }

        return $meta;
    }

    public function lastModified(string $path): FileAttributes
    {
        $meta = $this->getMetadata($path);

        if (! $meta || $meta->lastModified() === null) {
            throw UnableToRetrieveMetadata::lastModified($path);
        }

        return $meta;
    }

    public function fileSize(string $path): FileAttributes
    {
        $meta = $this->getMetadata($path);

        if (! $meta || $meta->fileSize() === null) {
            throw UnableToRetrieveMetadata::fileSize($path);
        }

        return $meta;
    }

    public function listContents(string $path, bool $deep): iterable
    {
        $prefixedPath = $this->prefixer->prefixPath($path);

        $response = $this->listObjects($prefixedPath, $deep);

        // 处理目录
        foreach ($response['CommonPrefixes'] ?? [] as $prefix) {
            yield new DirectoryAttributes($prefix['Prefix']);
        }

        foreach ($response['Contents'] ?? [] as $content) {
            yield new FileAttributes(
                $content['Key'],
                \intval($content['Size']),
                null,
                \strtotime($content['LastModified'])
            );
        }
    }

    public function move(string $source, string $destination, Config $config): void
    {
        $this->copy($source, $destination, $config);

        $this->delete($this->prefixer->prefixPath($source));
    }

    public function copy(string $source, string $destination, Config $config): void
    {
        $prefixedSource = $this->prefixer->prefixPath($source);

        $prefixedDestination = $this->prefixer->prefixPath($destination);

        $response = $this->minio->copyObject($prefixedSource, $prefixedDestination);

        if ($response['code'] != 200) {
            stdLog()->error('minio copy file error：' . ($response['error']['Message'] ?? '复制失败'), ['response' => $response]);
            throw UnableToCopyFile::fromLocationTo($source, $destination);
        }
    }

    public function getUrl(string $path): string
    {
        $prefixedPath = $this->prefixer->prefixPath($path);

        return $this->minio->getObjectUrl($prefixedPath);
    }

    public function getTemporaryUrl($path, int|string|\DateTimeInterface $expiration): string
    {
        if ($expiration instanceof \DateTimeInterface) {
            $expiration = $expiration->getTimestamp();
        }

        return $this->getSignedUrl($path, $expiration);
    }

    public function getSignedUrl(string $path, int|string $expires = '+60 minutes'): string
    {
        $prefixedPath = $this->prefixer->prefixPath($path);

        if (is_int($expires)) {
            $expires = \date('Y-m-d H:i:s', $expires);
        }

        return $this->getObjectClient()->getObjectSignedUrl($prefixedPath, $expires);
    }

    public function getObjectClient(): ObjectClient
    {
        return $this->objectClient ?? $this->objectClient = new ObjectClient($this->config);
    }

    public function getBucketClient(): BucketClient
    {
        return $this->bucketClient ?? $this->bucketClient = new BucketClient($this->config);
    }

    public function setObjectClient(ObjectClient $objectClient): CosAdapter
    {
        $this->objectClient = $objectClient;

        return $this;
    }

    public function setBucketClient(BucketClient $bucketClient): CosAdapter
    {
        $this->bucketClient = $bucketClient;

        return $this;
    }

    public function setVisibility(string $path, string $visibility): void
    {
    }

    protected function getSourcePath(string $path): string
    {
        return sprintf(
            '%s-%s.cos.%s.myqcloud.com/%s',
            $this->config['bucket'],
            $this->config['app_id'],
            $this->config['region'],
            $path
        );
    }

    protected function getMetadata($path): bool|FileAttributes
    {
        try {
            $prefixedPath = $this->prefixer->prefixPath($path);

            $meta = $this->getObjectClient()->headObject($prefixedPath)->getHeaders();
            if (empty($meta)) {
                return false;
            }
        } catch (\Throwable $e) {
            if ($e instanceof ClientException && $e->getCode() === 404) {
                return false;
            }

            throw $e;
        }

        return new FileAttributes(
            $path,
            isset($meta['Content-Length'][0]) ? \intval($meta['Content-Length'][0]) : null,
            null,
            isset($meta['Last-Modified'][0]) ? \strtotime($meta['Last-Modified'][0]) : null,
            $meta['Content-Type'][0] ?? null,
        );
    }

    protected function listObjects(string $directory = '', bool $recursive = false)
    {
        $result = $this->getBucketClient()->getObjects(
            [
                'prefix' => ((string) $directory === '') ? '' : ($directory . '/'),
                'delimiter' => $recursive ? '' : '/',
            ]
        )['ListBucketResult'];

        foreach (['CommonPrefixes', 'Contents'] as $key) {
            $result[$key] = $result[$key] ?? [];

            // 确保是二维数组
            if (($index = \key($result[$key])) !== 0) {
                $result[$key] = \is_null($index) ? [] : [$result[$key]];
            }

            //过滤掉目录
            if ($key === 'Contents') {
                $result[$key] = \array_filter($result[$key], function ($item) {
                    return ! \str_ends_with($item['Key'], '/');
                });
            }
        }

        return $result;
    }

    protected function normalizeVisibility(string $visibility): string
    {
        return match ($visibility) {
            Visibility::PUBLIC => 'public-read',
            Visibility::PRIVATE => 'private',
            default => 'default',
        };
    }
}
