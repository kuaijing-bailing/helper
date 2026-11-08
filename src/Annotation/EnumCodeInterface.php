<?php

declare(strict_types=1);
/**
 * This file is part of Kuaijing Bailing.
 *
 * @link     https://www.kuaijingai.com
 * @document https://help.kuaijingai.com
 * @contact  www.kuaijingai.com 7*12 9:00-21:00
 */
namespace Bailing\Annotation;

interface EnumCodeInterface
{
    /**
     * 获取错误信息.
     */
    public function getMsg(): ?string;

    /**
     * 获取错误码.
     */
    public function getCode(): ?int;

    /**
     * 获取前缀.
     */
    public function getPrefixCode(): ?int;

    /**
     * 获取i18n的内容.
     */
    public function getI18nMsg(?string $key = null): string|array|null;

    /**
     * 获取多语言提示；指定用户时直接返回该用户语言的单一字符串。
     * @param int $uid 目标用户编号，0 沿用请求语言；正数通过一分钟缓存查询用户语言
     * @param string $language 显式语言标签，非空时优先于用户语言
     */
    public function genI18nMsg(array $i18nParam = [], bool $returnNowLang = false, string $language = '', int $uid = 0): array|string;
}
