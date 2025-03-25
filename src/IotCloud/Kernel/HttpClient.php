<?php

declare(strict_types=1);
/**
 * This file is part of Kuaijing Bailing.
 *
 * @link     https://www.kuaijingai.com
 * @document https://help.kuaijingai.com
 * @contact  www.kuaijingai.com 7*12 9:00-21:00
 */
namespace Bailing\IotCloud\Kernel;

use Bailing\Helper\HttpHelper;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Swoole\Coroutine\System;

trait HttpClient
{
    protected $guzzleOptions = [];

    protected ?string $baseUri = null;

    public function request($method, $endpoint, $options = [])
    {
        try {
            $response = $this->getHttpClient()->{$method}($endpoint, $options);
            $statusCode = $response->getStatusCode();
            if ($statusCode != 200) {
                stdLog()->warning('IotCloud request error：' . $statusCode);
            }
        } catch (RequestException $e) {
            stdLog()->warning('IotCloud request error：' . $e->getMessage() . $e->getResponse()->getStatusCode());
            $response = $e->getResponse();
            $statusCode = $e->getResponse()->getStatusCode();
        }


        // accessToken超时了，尝试重试
        if ($statusCode == 401) {
            $newAccessToken = $this->refreshAccessToken();
            if (! empty($newAccessToken) && ! empty($options['headers']['Authorization'])) {
                $options['headers']['Authorization'] = 'Bearer ' . $newAccessToken;
            }
            try {
                $response = $this->getHttpClient()->{$method}($endpoint, $options);
            } catch (RequestException $e) {
                stdLog()->warning('IotCloud request error 401：' . $e->getMessage() . $e->getResponse()->getStatusCode());
                $response = $e->getResponse();
            }
        } elseif ($statusCode == 429) {
            System::sleep(5);
            try {
                $response = $this->getHttpClient()->{$method}($endpoint, $options);
            } catch (RequestException $e) {
                stdLog()->warning('IotCloud request error 429：' . $e->getMessage() . $e->getResponse()->getStatusCode());
                $response = $e->getResponse();
            }
        }

        return $this->unwrapResponse($response);
    }

    public function setGuzzleOptions(array $options)
    {
        $this->guzzleOptions = $options;
    }

    public function setBaseUri($uri)
    {
        $this->baseUri = trim($uri, '/');
        return $this;
    }

    protected function getHttpClient(): Client
    {
        $this->guzzleOptions['base_uri'] = $this->baseUri;
        return new Client($this->guzzleOptions);
    }

    /**
     * 统一转换响应结果为 json 格式.
     * @param $response
     *
     * @return mixed
     */
    protected function unwrapResponse($response)
    {
        $contentType = $response->getHeaderLine('Content-Type');
        $contents = $response->getBody()->getContents();
        if (stripos($contentType, 'json') !== false || stripos($contentType, 'javascript')) {
            return \json_decode($contents, true);
        }
        if (stripos($contentType, 'xml') !== false) {
            return \json_decode(\json_encode(\simplexml_load_string($contents)), true);
        }

        return $contents;
    }
}
