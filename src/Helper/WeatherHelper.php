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
    private const DEFAULT_HOURLY_FORECAST_HOURS = '24h';

    private const ALLOW_HOURLY_FORECAST_HOURS = ['24h', '72h', '168h'];

    /**
     * @param string $city 城市简称
     * @param string $province 省份简称（最好给到，避免地址重复）
     */
    #[Cacheable(prefix: 'getWeatherNow', value: '#{city}_#{province}_#{lang}', ttl: 3600, listener: 'getWeatherNow-update')]
    public static function getWeatherNow(string $city, string $province = '', string $lang = '')
    {
        $locationId = self::getCityLocationId($city, $province, $lang);
        if (! $locationId) {
            return [];
        }

        $client = new Client();
        $url = 'https://' . cfg('qweather_dev_host') . '/v7/weather/now?location=' . $locationId . '&key=' . cfg('qweather_dev_key');
        if (! empty($lang = self::normalizeLang($lang))) {
            $url .= '&lang=' . urlencode($lang);
        }
        try {
            $res = $client->request('GET', $url);
        } catch (GuzzleException $e) {
            stdLog()->warning('和风天气访问失败（' . $url . '）：' . $e->getMessage());
            return [];
        }

        $body = (string) $res->getBody();
        $bodyArr = Json::decode($body, true);
        if ($bodyArr['code'] != '200') {
            stdLog()->warning('和风天气访问失败（' . $url . '）：' . $bodyArr['code']);
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
    #[Cacheable(prefix: 'getWeatherCondition', value: '#{someDay}_#{city}_#{province}_#{lang}', ttl: 3600)]
    public static function getWeatherCondition(string $someDay, string $city, string $province = '', string $lang = '')
    {
        if (empty(cfg('qweather_dev_host'))) {
            return [];
        }

        $locationId = self::getCityLocationId($city, $province, $lang);
        if (! $locationId) {
            return [];
        }

        $client = new Client();
        $url = 'https://' . cfg('qweather_dev_host') . '/v7/weather/' . $someDay . '?location=' . $locationId . '&key=' . cfg('qweather_dev_key');
        if (! empty($lang = self::normalizeLang($lang))) {
            $url .= '&lang=' . urlencode($lang);
        }
        try {
            $res = $client->request('GET', $url);
        } catch (GuzzleException $e) {
            stdLog()->warning('和风天气访问失败（' . $url . '）：' . $e->getMessage());

            return [];
        }

        $body = (string) $res->getBody();
        $bodyArr = Json::decode($body, true);
        if ($bodyArr['code'] != '200') {
            stdLog()->warning('和风天气访问失败（' . $url . '）：' . $bodyArr['code']);

            return [];
        }

        return $bodyArr['daily'];
    }

    // 获取逐小时天气预报，默认返回24小时预报。
    #[Cacheable(prefix: 'getWeatherHourlyForecast', value: '#{city}_#{province}_#{lang}_#{hours}', ttl: 3600)]
    public static function getWeatherHourlyForecast(string $city, string $province = '', string $lang = '', string $hours = ''): array
    {
        if (empty(cfg('qweather_dev_host'))) {
            return [];
        }

        $locationId = self::getCityLocationId($city, $province, $lang);
        if (! $locationId) {
            return [];
        }

        $client = new Client();
        $hours = self::normalizeHourlyHours($hours);
        $url = 'https://' . cfg('qweather_dev_host') . '/v7/weather/' . $hours . '?location=' . $locationId . '&key=' . cfg('qweather_dev_key');
        if (! empty($lang = self::normalizeLang($lang))) {
            $url .= '&lang=' . urlencode($lang);
        }
        try {
            $res = $client->request('GET', $url);
        } catch (GuzzleException $e) {
            stdLog()->warning('和风天气访问失败（' . $url . '）：' . $e->getMessage());

            return [];
        }

        $body = (string) $res->getBody();
        $bodyArr = Json::decode($body, true);
        if ($bodyArr['code'] != '200') {
            stdLog()->warning('和风天气访问失败（' . $url . '）：' . $bodyArr['code']);

            return [];
        }

        return $bodyArr['hourly'] ?? [];
    }

    // 获取实时空气质量。
    #[Cacheable(prefix: 'getCurrentAirQuality', value: '#{latitude}_#{longitude}_#{lang}', ttl: 3600)]
    public static function getCurrentAirQuality(string $latitude, string $longitude, string $lang = ''): array
    {
        if (empty(cfg('qweather_dev_host'))) {
            return [];
        }

        $client = new Client();
        $url = 'https://' . cfg('qweather_dev_host') . '/airquality/v1/current/' . $latitude . '/' . $longitude . '?key=' . cfg('qweather_dev_key');
        if (! empty($lang = self::normalizeLang($lang))) {
            $url .= '&lang=' . urlencode($lang);
        }
        try {
            $res = $client->request('GET', $url);
        } catch (GuzzleException $e) {
            stdLog()->warning('和风天气访问失败（' . $url . '）：' . $e->getMessage());
            return [];
        }

        $body = (string) $res->getBody();
        $bodyArr = Json::decode($body, true);
        if (! is_array($bodyArr)) {
            stdLog()->warning('和风天气访问失败（' . $url . '）：empty body');
            return [];
        }
        if (! empty($bodyArr['code']) && $bodyArr['code'] != '200') {
            stdLog()->warning('和风天气访问失败（' . $url . '）：' . $bodyArr['code']);
            return [];
        }

        return $bodyArr;
    }

    /**
     * 2592000 = 86400 * 30.
     */
    #[Cacheable(prefix: 'weatherGetCityLocationId', value: '#{city}_#{province}_#{lang}', ttl: 2592000, listener: 'weatherGetCityLocationId-update')]
    public static function getCityLocationId(string $city, string $province = '', string $lang = '')
    {
        if (empty(cfg('qweather_dev_host'))) {
            return 0;
        }
        $client = new Client();
        if ($province) {
            $url = 'https://' . cfg('qweather_dev_host') . '/geo/v2/city/lookup?location=' . urlencode($city) . '&adm=' . urlencode($province) . '&key=' . cfg('qweather_dev_key');
        } else {
            $url = 'https://' . cfg('qweather_dev_host') . '/geo/v2/city/lookup?location=' . urlencode($city) . '&key=' . cfg('qweather_dev_key');
        }
        if (! empty($lang = self::normalizeLang($lang))) {
            $url .= '&lang=' . urlencode($lang);
        }
        try {
            $res = $client->request('GET', $url);
        } catch (GuzzleException $e) {
            stdLog()->warning('和风天气访问失败（' . $url . '）：' . $e->getMessage());
            return 0;
        }

        $body = (string) $res->getBody();
        $bodyArr = Json::decode($body, true);
        if ($bodyArr['code'] != '200') {
            stdLog()->warning('和风天气访问失败（' . $url . '）：' . $bodyArr['code']);
            return 0;
        }

        if (empty($bodyArr['location'])) {
            stdLog()->warning('和风天气查询失败（' . $url . '）：', $bodyArr['location'] ?? []);
            return 0;
        }

        return $bodyArr['location'][0]['id'];
    }

    private static function normalizeLang(string $lang): string
    {
        return match ($lang) {
            'zh_cn' => 'zh-hans',
            'zh_tw', 'zh_hk' => 'zh-hant',
            'en', 'de', 'es', 'fr', 'ja', 'ko', 'ru', 'th' => $lang,
            default => '',
        };
    }

    // 标准化逐小时预报时长，非法值使用默认24小时。
    private static function normalizeHourlyHours(string $hours): string
    {
        $hours = strtolower(trim($hours));
        if (in_array($hours, self::ALLOW_HOURLY_FORECAST_HOURS, true)) {
            return $hours;
        }

        return self::DEFAULT_HOURLY_FORECAST_HOURS;
    }
}
