<?php

declare(strict_types=1);
/**
 * This file is part of Kuaijing Bailing.
 *
 * @link     https://www.kuaijingai.com
 * @document https://help.kuaijingai.com
 * @contact  www.kuaijingai.com 7*12 9:00-21:00
 */
namespace Bailing\JsonRpc\Message;

interface NoticeServiceInterface
{
    /*
     * 添加机构通知消息.
     * @param int $orgId 机构ID，必填
     * @param array $uidArr 用户ID，必填
     * @param string $catLabel 消息分类，必填
     * @param string $title 消息标题，必填
     * @param array $content 消息体，数组，必填
     * @param string $remark 消息描述
     * @param string $uniqueId 业务唯一标识（用于接口标记消息状态）。不传值时，用户读消息后自动标记已完成。
     * @param string $link 业务链接标识
     * @param array $extra 额外参数，一般用于发送模板消息
     */
    public function addNotice(array $data): array;

    /**
     * 批量机构通知消息标记完成.
     */
    public function completeNoticeMany(int $orgId, array $uidUniqueArr, string $catLabel): array;

    /**
     * 机构通知消息标记完成.
     */
    public function completeNotice(int $orgId, int $uid, string $catLabel, string $uniqueId): array;

    /**
     * 机构用户事件通知.
     * @param int $orgId 机构ID
     * @param int $uid 用户ID
     * @param string $eventType 事件类型，例如 task 任务
     * @param string $count 事件统计值、
     */
    public function eventExpiredCount(int $orgId, int $uid, string $eventType, int $count): array;
}
