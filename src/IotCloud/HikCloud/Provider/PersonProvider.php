<?php

declare(strict_types=1);
/**
 * This file is part of Kuaijing Bailing.
 *
 * @link     https://www.kuaijingai.com
 * @document https://help.kuaijingai.com
 * @contact  www.kuaijingai.com 7*12 9:00-21:00
 */
namespace Bailing\IotCloud\HikCloud\Provider;

use Bailing\IotCloud\HikCloud\AbstractProvider;

class PersonProvider extends AbstractProvider
{
    /**
     * 新增住户人员信息。
     * 注意：
     * 1、本篇住户专指业主、家属、租客三个身份类别的人员。
     * 2、“姓名”+“手机号”或者“证件类型”+“证件号码”至少有一组信息完整。
     *
     * @return mixed
     */
    public function addPerson(array $params)
    {
        return $this->postJson('/api/v1/estate/system/person', $params);
    }

    /**
     * 删除住户人员信息。
     * @param $personId
     *
     * @return mixed
     */
    public function deletePerson(string $personId)
    {
        $endpoint = '/api/v1/estate/system/person/' . $personId;
        return $this->deleteJson($endpoint, ['personId' => $personId]);
    }

    /**
     * 修改住户人员的基础信息。
     *
     * @return mixed
     */
    public function updatePerson(array $params)
    {
        return $this->postJson('/api/v1/estate/system/person/actions/updatePerson', $params);
    }

    /**
     * 设置住户人员所属社区。
     *
     * @return mixed
     */
    public function addCommunityRelation(array $params)
    {
        return $this->postJson('/api/v1/estate/system/person/actions/addCommunityRelation', $params);
    }

    /**
     * 删除住户人员与社区的关联关系。
     * 若住户在该社区有所属房屋，将同步解除住户与房屋的所属关系。
     *
     * @return mixed
     */
    public function deleteCommunityRelation(array $params)
    {
        return $this->postJson('/api/v1/estate/system/person/actions/deleteCommunityRelation', $params);
    }

    /**
     * 设置人员所属户室（人员和房屋关联之后，将自动下发权限到设备）。支持审核流程，如需修改审核方式，请在社区管理页面进行配置。审核结果将通过消息订阅进行通知，消息类型为community_message_audit_state。
     *
     * @return mixed
     */
    public function addRoomRelation(array $params)
    {
        return $this->postJson('/api/v1/estate/system/person/actions/addRoomRelation', $params);
    }

    /**
     * 删除住户人员所属户室（后台将同步删除设备上的权限）。
     *
     * @return mixed
     */
    public function deleteRoomRelation(array $params)
    {
        return $this->postJson('/api/v1/estate/system/person/actions/deleteRoomRelation', $params);
    }

    /**
     * 重置人员所属户室.
     *
     * @return mixed
     */
    public function setRoomRelation(array $params)
    {
        return $this->postJson('/api/v1/estate/system/person/actions/setRoomRelation', $params);
    }

    /**
     * 获取人员户室信息.
     */
    public function roomList(array $params)
    {
        return $this->getJson('/api/v1/estate/system/person/actions/roomList', $params);
    }

    /**
     * 人员查询.
     *
     * @return mixed
     */
    public function personInfoList(array $params)
    {
        return $this->postJson('/api/v1/estate/system/person/actions/personInfoList', $params);
    }

    /**
     * 获取人员标签列表.
     *
     * @return mixed
     */
    public function labelList(array $params)
    {
        return $this->getJson('/api/v1/estate/system/person/actions/labelList', $params);
    }

    /**
     * 设置人员标签和车牌号.
     *
     * @return mixed
     */
    public function addLabelAndLicenseRelation(array $params)
    {
        return $this->postJson('/api/v1/estate/system/person/actions/addLabelAndLicenseRelation', $params);
    }

    /**
     * 社区访客预约登记。
     *
     * @return mixed
     */
    public function addVisitor(array $params)
    {
        return $this->postJson('/api/v1/estate/visitors', $params);
    }

    public function deleteVisitor(string $reservationId)
    {
        $endpoint = "/api/v1/estate/visitors/{$reservationId}";
        return $this->deleteJson($endpoint, ['reservationId' => $reservationId]);
    }
}
