<?php

declare(strict_types=1);
/**
 * This file is part of Kuaijing Bailing.
 *
 * @link     https://www.kuaijingai.com
 * @document https://help.kuaijingai.com
 * @contact  www.kuaijingai.com 7*12 9:00-21:00
 */
namespace Bailing\Command;

use Bailing\Helper\FileHelper;
use Bailing\Helper\HttpHelper;
use Hyperf\Codec\Json;
use Hyperf\Command\Annotation\Command;
use Hyperf\Command\Command as HyperfCommand;
use Symfony\Component\Console\Input\InputOption;

#[Command]
class DevGetComposerCommand extends HyperfCommand
{
    public function __construct()
    {
        parent::__construct('dev:get_composer');
    }

    public function configure()
    {
        parent::configure();
        $this->setDescription('从局域网电脑快速复制composer包');
        $this->addOption('ip', 'I', InputOption::VALUE_REQUIRED, '局域网IP地址', '');
    }

    public function handle()
    {
        $ip = $this->input->getOption('ip');
        if (empty($ip)) {
            $this->line('请输入IP地址!', 'error');
            return;
        }


        $this->line('开始判断当前bailing版本!', 'info');
        $composerJson = Json::decode(file_get_contents(BASE_PATH . '/composer.json'));
        $bailingVersion = str_replace(['^', '~'], '', $composerJson['require']['bailing/helper']);


        $this->line('开始下载composer包!', 'info');
        $localPath = BASE_PATH . '/runtime/composer.zip';
        $composerUnzipDir = BASE_PATH . '/runtime/composer';
        file_exists($localPath) && @unlink($localPath);
        file_exists($composerUnzipDir) && FileHelper::delDir($composerUnzipDir);

        HttpHelper::downloadFile('http://' . $ip . '/downloadComposer.php', $localPath, 'get', [
            'version' => $bailingVersion,
            'app_name' => env('APP_NAME'),
        ]);

        if (! file_exists($localPath)) {
            $this->line('文件下载失败，请重试！', 'error');
            return;
        }


        $this->line('开始解压composer包!', 'info');
        $zip = new \ZipArchive();
        if ($zip->open($localPath) === true) {
            $zip->extractTo($composerUnzipDir);
            $zip->close();
        }

        copy($composerUnzipDir . '/composer.lock', BASE_PATH . '/composer.lock');
        FileHelper::delDir(BASE_PATH . '/vendor');
        FileHelper::copyDir($composerUnzipDir . '/vendor', BASE_PATH . '/vendor');
    }
}
