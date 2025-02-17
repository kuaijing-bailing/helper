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

use Bailing\Annotation\EnumI18n;
use Bailing\Annotation\EnumI18nGroup;
use Bailing\Helper\EnumStore;
use Bailing\Helper\Intl\I18nHelper;
use Bailing\Model\BailingI18nTranslation;
use Hyperf\Contract\TranslatorInterface;
use ReflectionEnum;
use ReflectionEnumUnitCase;

trait EnumI18nGet
{
    /**
     * @return array{prefixCode:null|int,prefixMsg:null|string}
     */
    public static function getEnumsGroupCode(): array
    {
        $res = self::getEnumClassAttitude();
        return [
            'groupCode' => $res->groupCode ?? null,
        ];
    }

    /**
     * 获取所有的文本内容.
     */
    public function getTxtArr(): ?array
    {
        return self::getEnums();
    }

    /**
     * 获取文本内容.
     */
    public function getTxt(): ?string
    {
        return self::getEnums()[$this->name]['txt'] ?? null;
    }

    /**
     * 将枚举转换为数组.
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'value' => $this->value,
            'txt' => $this->getTxt(),
            'i18nTxt' => $this->getI18nTxt(),
            'group' => [
                'groupCode' => $this->getI18nGroupCode(),
            ],
        ];
    }

    /**
     * 获取i18n的内容.
     */
    public function getI18nTxt(?string $key = null): string|array|null
    {
        if ($key !== null) {
            return self::getEnums()[$this->name]['i18nTxt'][$key] ?? null;
        }

        return self::getEnums()[$this->name]['i18nTxt'] ?? null;
    }

    /**
     * 获取i18n的组装内容，用于返回.
     * @param array $i18nParam i18n参数
     */
    public function genI18nTxt(array $i18nParams = [], bool $returnNowLang = false, string $language = ''): array|string
    {
        $txtArr = self::getEnums()[$this->name];

        if ($returnNowLang) {
            $nowLang = I18nHelper::getNowLang($language);
            $txt = $txtArr['i18nTxt'][$nowLang] ?? $txtArr['txt'];
            foreach ($i18nParams as $key => $value) {
                $txt = str_replace(sprintf('{%s}', $key), $value, $txt);
            }
            return $txt;
        }

        return [
            'value' => $txtArr['txt'],
            'i18n_value' => $txtArr['i18nTxt'],
            'i18n_key' => $txtArr['i18nKey'],
            'i18n_params' => $i18nParams,
        ];
    }

    /**
     * 获取错误码前缀.
     */
    public function getI18nGroupCode(): ?int
    {
        return self::getEnums()[$this->name]['group']['groupCode'] ?? null;
    }

    public static function getEnums(): array
    {
        $enum = new ReflectionEnum(static::class);
        if (EnumStore::isset($enum->getName())) {
            return EnumStore::get($enum->getName());
        }
        $enumCases = $enum->getCases();
        $classObj = self::getEnumClassAttitude();

        // 读取该分组下所有的多语言，以data_id作为键，value作为内容
        $langList = BailingI18nTranslation::query()->where(['type' => 0, 'group_code' => $classObj->groupCode])->pluck('value', 'data_id')->toArray();

        foreach ($enumCases as $enumCase) {
            /** @var self $case */
            $case = $enumCase->getValue();
            $obj = $case->getEnumCase();

            $caseArr = [
                'name' => $case->name,
                'value' => $case->value,
                'txt' => $obj->txt,
                'i18nTxt' => $langList[$case->value] ?? $obj->i18nTxt,
                'group' => [
                    'groupCode' => $classObj->groupCode,
                ],
                'reported' => !empty($langList[$case->value]), // 曾经上报保存过多语言
            ];
            $caseArr['i18nKey'] = 'i18n.' . env('APP_NAME') . '.' . $caseArr['group']['groupCode'] . '.' . $caseArr['value'];

            EnumStore::set($enum->getName(), $case->name, $caseArr);
        }

        return EnumStore::get($enum->getName());
    }

    protected static function getEnumClassAttitude(): ?EnumI18nGroup
    {
        return (new ReflectionEnum(static::class))->getAttributes(EnumI18nGroup::class)[0]->newInstance() ?? null;
    }

    protected function getEnumCase(): ?EnumI18n
    {
        return (new ReflectionEnumUnitCase($this, $this->name))->getAttributes(EnumI18n::class)[0]->newInstance() ?? null;
    }
}
