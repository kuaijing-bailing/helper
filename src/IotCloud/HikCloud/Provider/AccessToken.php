<?php

declare(strict_types=1);
/**
 * This file is part of Kuaijing Bailing.
 *
 * @link     https://www.kuaijingai.com
 * @document https://help.kuaijingai.com
 * @contact  www.kuaijingai.com 7*12 9:00-21:00
 */
namespace Bailing\IotCloud\HikCloud\Provider;

trait AccessToken
{
    public function getAccessToken()
    {
        $params = [
            'client_id' => trim($this->config->getHikConfig()['client_id']),
            'client_secret' => trim($this->config->getHikConfig()['client_secret']),
            'grant_type' => 'client_credentials',
        ];

        if (empty($params['client_id']) || empty($params['client_secret'])) {
            stdLog()->warning('HikCloud getAccessToken Error', ['params' => $params, 'hikConfig' => $this->config->getHikConfig()]);
            return null;
        }

        $redisKey = sprintf('hikCloudAccessToken:%s:%s', $params['client_id'], $params['client_secret']);
        $accessToken = redis()->get($redisKey);
        if (! empty($accessToken)) {
            return $accessToken;
        }
        $options = [
            'headers' => [],
            'form_params' => $params,
        ];
        $result = $this->setBaseUri('https://api2.hik-cloud.com')->request('POST', '/oauth/token', $options);

        if (empty($result['access_token'])) {
            return null;
        }

        // 缓存到有效期前10分钟
        redis()->set($redisKey, $result['access_token'], $result['expires_in'] - 600);

        return $result['access_token'];
    }

    public function refreshAccessToken()
    {
        $params = [
            'client_id' => trim($this->config->getHikConfig()['client_id']),
            'client_secret' => trim($this->config->getHikConfig()['client_secret']),
        ];

        if (empty($params['client_id']) || empty($params['client_secret'])) {
            return null;
        }

        $redisKey = sprintf('hikCloudAccessToken:%s:%s', $params['client_id'], $params['client_secret']);
        redis()->del($redisKey);

        return $this->getAccessToken();
    }
}
