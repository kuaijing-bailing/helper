<?php

declare(strict_types=1);
/**
 * This file is part of Kuaijing Bailing.
 *
 * @link     https://www.kuaijingai.com
 * @document https://help.kuaijingai.com
 * @contact  www.kuaijingai.com 7*12 9:00-21:00
 */

namespace Bailing\Controller;

use Bailing\Annotation\RateRequest;
use Bailing\Constants\Code\Common\CommonCode;
use Bailing\Helper\ApiHelper;
use Bailing\Helper\OrgConfigHelper;
use Bailing\Helper\StrHelper;
use Bailing\Middleware\SystemMiddleware;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\PutMapping;

#[Controller]
#[Middleware(SystemMiddleware::class)]
class OrgConfigController
{
    /**
     * 读取机构配置；with_document 为 1 时，为单个配置附带注释原文及服务支持状态.
     */
    #[GetMapping(path: '/common/orgConfig/get')]
    public function get(): array
    {
        $orgIdInput = request()->input('org_id');
        $orgId = filter_var($orgIdInput, FILTER_VALIDATE_INT);
        $name = request()->input('name', '');
        $index = StrHelper::mb_trim(strval(request()->input('index', '')));

        if ($orgIdInput === null || $orgId === false || $orgId < 0 || empty($name)) {
            return ApiHelper::genErrorData(CommonCode::PARAMS_EMPTY_WITH_FIELD->genI18nMsg(['field' => 'org_id, name']));
        }

        if ((int) request()->input('with_document', 0) === 1 && is_string($name)) {
            return ApiHelper::genSuccessData(OrgConfigHelper::getConfigForEditor($orgId, StrHelper::mb_trim($name), $index));
        }

        $result = $this->fetchConfig($orgId, $name, $index);

        return ApiHelper::genSuccessData(['result' => $result]);
    }

    /**
     * 查询当前机构由系统后台保存的配置名称，跨 index 去重，供编辑器自由选择.
     */
    #[GetMapping(path: '/common/orgConfig/names')]
    public function names(): array
    {
        $orgIdInput = request()->input('org_id');
        $orgId = filter_var($orgIdInput, FILTER_VALIDATE_INT);
        if ($orgIdInput === null || $orgId === false || $orgId < 0) {
            return ApiHelper::genErrorData(CommonCode::PARAMS_WRONG_WITH_FIELD->genI18nMsg(['field' => 'org_id']));
        }

        return ApiHelper::genSuccessData(['names' => OrgConfigHelper::getSystemConfigNames($orgId)]);
    }

    /**
     * 查询当前机构及名称下由系统后台保存的 index，空字符串表示无 index 的配置.
     */
    #[GetMapping(path: '/common/orgConfig/indexes')]
    public function indexes(): array
    {
        $orgIdInput = request()->input('org_id');
        $orgId = filter_var($orgIdInput, FILTER_VALIDATE_INT);
        $name = request()->input('name');
        if ($orgIdInput === null || $orgId === false || $orgId < 0 || ! is_string($name) || StrHelper::mb_trim($name) === '') {
            return ApiHelper::genErrorData(CommonCode::PARAMS_WRONG_WITH_FIELD->genI18nMsg(['field' => 'org_id, name']));
        }

        return ApiHelper::genSuccessData(['indexes' => OrgConfigHelper::getSystemConfigIndexes($orgId, StrHelper::mb_trim($name))]);
    }

    /**
     * 保存配置并由系统后台接口标记来源；value_document 保存注释，value 保持业务读取格式.
     */
    #[PutMapping(path: '/common/orgConfig/set')]
    #[RateRequest]
    public function set(): array
    {
        $orgIdInput = request()->input('org_id');
        $orgId = filter_var($orgIdInput, FILTER_VALIDATE_INT);
        $name = StrHelper::mb_trim(strval(request()->input('name', '')));
        $value = StrHelper::mb_trim(strval(request()->input('value', '')));
        $index = StrHelper::mb_trim(strval(request()->input('index', '')));
        $valueDocument = request()->input('value_document');

        if ($orgIdInput === null || $orgId === false || $orgId < 0) {
            return ApiHelper::genErrorData(CommonCode::PARAMS_WRONG_WITH_FIELD->genI18nMsg(['field' => 'org_id']));
        }
        if ($name === '' || $value === '') {
            return ApiHelper::genErrorData(CommonCode::PARAMS_EMPTY_WITH_FIELD->genI18nMsg(['field' => 'name, value']));
        }
        if ($valueDocument !== null && ! is_string($valueDocument)) {
            return ApiHelper::genErrorData(CommonCode::PARAMS_WRONG_WITH_FIELD->genI18nMsg(['field' => 'value_document']));
        }
        try {
            $result = OrgConfigHelper::setConfig($orgId, $name, $value, $index, valueDocument: $valueDocument, fromSystem: true);
        } catch (\JsonException) {
            return ApiHelper::genErrorData(CommonCode::CONFIG_DOCUMENT_INVALID);
        }

        return ApiHelper::genSuccessData(['result' => $result]);
    }

    /**
     * 读取配置：name 为字符串时返回字符串，为数组时返回以 name 为键的数组.
     */
    private function fetchConfig(int $orgId, array|string $name, string $index): array|string
    {
        if (is_array($name)) {
            $result = [];
            foreach ($name as $configName) {
                $configName = StrHelper::mb_trim(strval($configName));
                if (empty($configName)) {
                    continue;
                }
                $result[$configName] = OrgConfigHelper::getConfig($orgId, $configName, $index);
            }
            return $result;
        }

        return OrgConfigHelper::getConfig($orgId, StrHelper::mb_trim(strval($name)), $index);
    }
}
