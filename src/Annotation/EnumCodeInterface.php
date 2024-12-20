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
     * 获取i18n的组装内容，用于返回.
     */
    public function genI18nMsg(array $i18nParam = [], bool $returnNowLang = false, string $language = ''): array|string;
}
