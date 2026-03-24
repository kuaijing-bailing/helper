<?php

declare(strict_types=1);
/**
 * This file is part of Kuaijing Bailing.
 *
 * @link     https://www.kuaijingai.com
 * @document https://help.kuaijingai.com
 * @contact  www.kuaijingai.com 7*12 9:00-21:00
 */
namespace Bailing\JsonRpc\Org;

interface OrgOrderServiceInterface
{
    /**
     * 添加机构和项目绑定关系.
     * @param array $payData 支付信息
     */
    public function billOrderAfterPay(array $payData): array;
}
