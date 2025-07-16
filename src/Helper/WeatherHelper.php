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

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Hyperf\Cache\Annotation\Cacheable;
use Hyperf\Codec\Json;

class WeatherHelper
{
    /**
     * @param string $city 城市简称
     * @param string $province 省份简称（最好给到，避免地址重复）
     */
    #[Cacheable(prefix: 'getWeatherNow', value: '#{city}_#{province}', ttl: 3600, listener: 'getWeatherNow-update')]
    public static function getWeatherNow(string $city, string $province = '')
    {
        $locationId = self::getCityLocationId($city, $province);
        if (! $locationId) {
            return [];
        }

        $client = new Client();
        $url = 'https://' . cfg('qweather_dev_host') . '/v7/weather/now?location=' . $locationId . '&key=' . cfg('qweather_dev_key');
        try {
            $res = $client->request('GET', $url);
        } catch (GuzzleException $e) {
            stdLog()->error('和风天气访问失败（' . $url . '）：' . $e->getMessage());
            return [];
        }

        $body = (string) $res->getBody(); // 获取响应体，对象
        $bodyArr = Json::decode($body, true);
        if ($bodyArr['code'] != '200') {
            stdLog()->error('和风天气访问失败（' . $url . '）：' . $bodyArr['code']);
            return [];
        }

        return $bodyArr['now'];
    }

    /**
     * @param string $someDay 查询x天的天气
     * @param string $city 城市简称
     * @param string $province 省份简称（最好给到，避免地址重复）
     *                         someDay:
     *                         3d 3天预报。
     *                         7d 7天预报。
     *                         10d 10天预报。
     *                         15d 15天预报。
     *                         30d 30天预报。
     */
    #[Cacheable(prefix: 'getWeatherCondition', value: '#{someDay}_#{city}_#{province}', ttl: 3600)]
    public static function getWeatherCondition(string $someDay, string $city, string $province = '')
    {
        $locationId = self::getCityLocationId($city, $province);
        if (! $locationId) {
            return [];
        }

        $client = new Client();
        $url = 'https://' . cfg('qweather_dev_host') . '/v7/weather/' . $someDay . '?location=' . $locationId . '&key=' . cfg('qweather_dev_key');
        try {
            $res = $client->request('GET', $url);
        } catch (GuzzleException $e) {
            stdLog()->error('和风天气访问失败（' . $url . '）：' . $e->getMessage());

            return [];
        }

        $body = (string) $res->getBody(); // 获取响应体，对象
        $bodyArr = Json::decode($body, true);
        if ($bodyArr['code'] != '200') {
            stdLog()->error('和风天气访问失败（' . $url . '）：' . $bodyArr['code']);

            return [];
        }

        return $bodyArr['daily'];
    }

    /**
     * 2592000 = 86400 * 30.
     */
    #[Cacheable(prefix: 'weatherGetCityLocationId', value: '#{city}_#{province}', ttl: 2592000, listener: 'weatherGetCityLocationId-update')]
    public static function getCityLocationId(string $city, string $province = '')
    {
        $client = new Client();
        if ($province) {
            $url = 'https://' . cfg('qweather_dev_host') . '/geo/v2/city/lookup?location=' . urlencode($city) . '&adm=' . urlencode($province) . '&key=' . cfg('qweather_dev_key');
        } else {
            $url = 'https://' . cfg('qweather_dev_host') . '/geo/v2/city/lookup?location=' . urlencode($city) . '&key=' . cfg('qweather_dev_key');
        }
        try {
            $res = $client->request('GET', $url);
        } catch (GuzzleException $e) {
            stdLog()->error('和风天气访问失败（' . $url . '）：' . $e->getMessage());
            return 0;
        }

        $body = (string) $res->getBody(); // 获取响应体，对象
        $bodyArr = Json::decode($body, true);
        if ($bodyArr['code'] != '200') {
            stdLog()->error('和风天气访问失败（' . $url . '）：' . $bodyArr['code']);
            return 0;
        }

        if (empty($bodyArr['location'])) {
            stdLog()->error('和风天气查询失败（' . $url . '）：', $bodyArr['location']);
            return 0;
        }

        return $bodyArr['location'][0]['id'];
    }
}
