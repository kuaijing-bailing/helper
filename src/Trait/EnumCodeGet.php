<?php

declare(strict_types=1);
/**
 * This file is part of Kuaijing Bailing.
 *
 * @link     https://www.kuaijingai.com
 * @document https://help.kuaijingai.com
 * @contact  www.kuaijingai.com 7*12 9:00-21:00
 */
namespace Bailing\Trait;

use Bailing\Annotation\EnumCode;
use Bailing\Annotation\EnumCodePrefix;
use Bailing\Helper\EnumStore;
use Bailing\Helper\Intl\I18nHelper;
use ReflectionEnum;
use ReflectionEnumUnitCase;

trait EnumCodeGet
{
    public function __call(string $name, array $arguments)
    {
        $ext = $this->getExt();
        $pos = stripos($name, 'get');
        if ($pos === 0) {
            $getKey = substr($name, 3);
            if ($getKey) {
                $getKey = strtolower(substr($getKey, 0, 1)) . substr($getKey, 1);
                if (isset($ext[$getKey])) {
                    return $ext[$getKey];
                }
            }
        }
        if (isset($ext[$name])) {
            return $ext[$name];
        }

        return null;
    }

    /**
     * @return array{prefixCode:null|int,prefixMsg:null|string}
     */
    public static function getEnumsPrefix(): array
    {
        $res = self::getEnumClassAttitude();
        return [
            'prefixCode' => $res->prefixCode ?? null,
        ];
    }

    /**
     * 获取错误信息.
     */
    public function getMsg(): ?string
    {
        return self::getEnums()[$this->name]['msg'] ?? null;
    }

    /**
     * 获取错误码.
     */
    public function getCode(): ?int
    {
        return self::getEnums()[$this->name]['code'] ?? null;
    }

    /**
     * 将枚举转换为数组.
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'value' => $this->value,
            'msg' => $this->getMsg(),
            'code' => $this->getCode(),
            'i18nMsg' => $this->getI18nMsg(),
            'pre' => [
                'prefixCode' => $this->getPrefixCode(),
            ],
        ];
    }

    /**
     * 获取i18n的内容.
     */
    public function getI18nMsg(?string $key = null): string|array|null
    {
        if ($key !== null) {
            return self::getEnums()[$this->name]['i18nMsg'][$key] ?? null;
        }

        return self::getEnums()[$this->name]['i18nMsg'] ?? null;
    }

    /**
     * 获取i18n的组装内容，用于返回.
     * @param array $i18nParam i18n参数
     */
    public function genI18nMsg(array $i18nParam = [], bool $returnNowLang = false, string $language = ''): array|string
    {
        $msgArr = self::getEnums()[$this->name];

        // 返回当前语言的字符串，一般用于服务间的错误.
        if ($returnNowLang) {
            if(!empty($language)){
                $nowLang = I18nHelper::getNowLang($language);
            } else {
                $nowLang = cfg('lang_default') ?: 'zh_cn';
            }
            $msg = $msgArr['i18nMsg'][$nowLang] ?? $msgArr['msg'];
            foreach ($i18nParam as $key => $value) {
                $msg = str_replace(sprintf('{%s}', $key), $value, $msg);
            }
            return $msg;
        }

        // 替换 i18n 的参数
        if (! empty($i18nParam)) {
            foreach ($i18nParam as $key => $value) {
                $msgArr['msg'] = str_replace(sprintf('{%s}', $key), $value, $msgArr['msg']);
            }
        }

        return [
            'msg' => $msgArr['msg'],
            'i18n_msg_key' => $msgArr['i18nKey'],
            'i18n_msg_param' => $i18nParam,
        ];
    }

    /**
     * 获取错误码前缀.
     */
    public function getPrefixCode(): ?int
    {
        return self::getEnums()[$this->name]['pre']['prefixCode'] ?? null;
    }

    public static function getEnums(): array
    {
        $enum = new ReflectionEnum(static::class);
        if (EnumStore::isset($enum->getName())) {
            return EnumStore::get($enum->getName());
        }

        $isBailing = str_contains($enum->getName(), 'Bailing\Constants\Code');

        $enumCases = $enum->getCases();
        $classObj = self::getEnumClassAttitude();
        foreach ($enumCases as $enumCase) {
            /** @var self $case */
            $case = $enumCase->getValue();
            $obj = $case->getEnumCase();

            $caseArr = [
                'name' => $case->name,
                'value' => $case->value,
                'msg' => $obj->msg,
                'code' => (int) (($classObj->prefixCode) . ($case->value < 10 ? '0' . $case->value : $case->value)),
                'i18nMsg' => $obj->i18nMsg,
                'pre' => [
                    'prefixCode' => $classObj->prefixCode,
                ],
            ];
            $caseArr['i18nKey'] = 'code.' . ($isBailing ? 'common' : env('MICRO_NAME', env('APP_NAME'))) . '.' . $caseArr['code'];

            EnumStore::set($enum->getName(), $case->name, $caseArr);
        }

        return EnumStore::get($enum->getName());
    }

    protected static function getEnumClassAttitude(): ?EnumCodePrefix
    {
        return (new ReflectionEnum(static::class))->getAttributes(EnumCodePrefix::class)[0]->newInstance() ?? null;
    }

    protected function getEnumCase(): ?EnumCode
    {
        return (new ReflectionEnumUnitCase($this, $this->name))->getAttributes(EnumCode::class)[0]->newInstance() ?? null;
    }
}
