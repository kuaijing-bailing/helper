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

use Bailing\Helper\ApiHelper;
use Hyperf\RpcClient\AbstractServiceClient;

class NoticeServiceConsumer extends AbstractServiceClient implements NoticeServiceInterface
{
    /**
     * 定义对应服务提供者的服务名称.
     */
    protected string $serviceName = 'NoticeService';

    /**
     * 定义对应服务提供者的服务协议.
     */
    protected string $protocol = 'jsonrpc-http';

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
    public function addNotice(array $data): array
    {
        try {
            return $this->__request(__FUNCTION__, compact('data'));
        } catch (\Exception $exception) {
            return ApiHelper::genServiceErrorData($this->serviceName, $exception);
        }
    }

    /**
     * 批量机构通知消息标记完成.
     */
    public function completeNoticeMany(int $orgId, array $uidUniqueArr, string $catLabel): array
    {
        try {
            return $this->__request(__FUNCTION__, compact('orgId', 'uidUniqueArr', 'catLabel'));
        } catch (\Exception $exception) {
            return ApiHelper::genServiceErrorData($this->serviceName, $exception);
        }
    }

    /**
     * 机构通知消息标记完成.
     */
    public function completeNotice(int $orgId, int $uid, string $catLabel, string $uniqueId): array
    {
        try {
            return $this->__request(__FUNCTION__, compact('orgId', 'uid', 'catLabel', 'uniqueId'));
        } catch (\Exception $exception) {
            return ApiHelper::genServiceErrorData($this->serviceName, $exception);
        }
    }

    /**
     * 机构用户事件数统计通知.
     * @param int $orgId 机构ID
     * @param int $uid 用户ID
     * @param string $eventType 事件类型，例如 task 任务
     * @param string $count 事件统计值、
     */
    public function eventExpiredCount(int $orgId, int $uid, string $eventType, int $count): array
    {
        try {
            return $this->__request(__FUNCTION__, compact('orgId', 'uid', 'eventType', 'count'));
        } catch (\Exception $exception) {
            return ApiHelper::genServiceErrorData($this->serviceName, $exception);
        }
    }

    /**
     * 群聊添加额外信息.
     *
     * @param int $orgId 机构ID
     */
    public function addChat(int $orgId, array $chatInfo): array
    {
        try {
            return $this->__request(__FUNCTION__, compact('orgId', 'chatInfo'));
        } catch (\Exception $exception) {
            return ApiHelper::genServiceErrorData($this->serviceName, $exception);
        }
    }

    /**
     * 群聊添加额外信息.
     *
     * @param int $orgId 机构ID
     */
    public function updateChatExtra(int $orgId, string $alias, int $businessId, array $extra): array
    {
        try {
            return $this->__request(__FUNCTION__, compact('orgId', 'alias', 'businessId', 'extra'));
        } catch (\Exception $exception) {
            return ApiHelper::genServiceErrorData($this->serviceName, $exception);
        }
    }
}
