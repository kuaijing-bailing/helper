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

interface OrgServiceInterface
{
    /**
     * 添加机构和项目绑定关系.
     * @param array $data 二维数组 [['org_id' => '', 'village_id' => ''], ['org_id' => '', 'village_id' => '']]
     */
    public function addOrgVillageRelation(array $data): array;

    /**
     * 获取用户管理的机构.
     * @param int $uid 用户ID
     */
    public function getOrgByAdminUid(int $uid): array;

    /**
     * 通过机构ID获取项目的绑定关系.
     * @param int $orgId 机构ID
     */
    public function getVillageRelationByOrgId(int $orgId): array;

    /**
     * 根据ID数组获取房源标签列表.
     */
    public function getTagHouseList(array $tagIdArr, int $orgId = 0, int $userShow = -1): array;

    /**
     * 根据ID数组获取楼栋标签列表.
     */
    public function getTagBuildList(array $tagIdArr, int $orgId = 0, int $userShow = -1): array;

    /**
     * 根据ID数组获取项目标签列表.
     */
    public function getTagVillageList(array $tagIdArr, int $orgId = 0, int $userShow = -1): array;

    /**
     * 楼宇绑定收支账户.
     */
    public function buildBindAccount(array $accountIds, int $buildId, string $buildName): array;

    /**
     * 根据ID 获取用户在机构内的信息.
     */
    public function getUserByUserIdArr(array $userIdArr, int $orgId = 0): array;

    /**
     * 根据ID 获取机构的基本信息.
     */
    public function getOrgByOrgIdArr(array|int $orgIdArr): array;

    /**
     * 根据域名前缀获取机构的基本信息.
     */
    public function getOrgByDomainPrefix(string $domainPrefix): array;

    /**
     * 根据ID 获取租客基本信息列表.
     */
    public function getOwnerByIdArr(array $idArr, int $orgId = 0): array;

    /**
     * 根据指定owner_id获取合同关联房间信息.
     */
    public function getRoomIdByOwner(int $ownerId): array;

    /**
     * 根据指定owner_id/指定条件获取机构账单信息.
     */
    public function getBillListByOwner(int $ownerId, array $where): array;

    /**
     * 根据指定账单ID获取账单详情(含子账单明细).
     */
    public function getBillDetailById(int $billId, array $where): array;

    /**
     * 根据指定uid获取机构绑定的最新登录的用户信息.
     */
    public function getOrgUserDataByUid(int $userId, int $orgId = 0, array $fields = []): array;

    /**
     * 根据指定uid获取机构绑定的楼层管理的权限.
     */
    public function getOrgUserManageLayersByUid(int $userId, int $orgId, int $buildId = 0): array;

    /**
     * 获取部门和人员列表.
     */
    public function getOrgDepartmentUser(array $departIdArr, array $userIdArr, int $orgId): array;

    /**
     * 通过指定楼宇id获取租客列表.
     */
    public function getOwnerByBuildId(int $buildId, int $overdueDay = 0): array;

    /**
     * 通过指定项目id获取租客列表.
     */
    public function getOwnerByVillageId(int $villageId, int $overdueDay = 0): array;

    /**
     * 通过租客ID获取其所有合同对应的项目、楼栋ID.
     */
    public function getVillageBuildByOwner(int $ownerId, int $overdueDay = 0): array;

    /**
     * 水电抄表同步账单数据至org服务接管
     */
    public function energyOrderHandel(int $orgId, array $billArr): array;

    /**
     * 根据指定房间id获取合同列表 return: 租客名称、租赁面积、物业面积、起止日期、状态
     */
    public function getContractByRoom(int $orgId, int $roomId): array;

    /**
     * 根据指定项目id获取项目下所有楼宇在租房间的租金收益.
     */
    public function getVillageRentalIncome(int $orgId, int $villageId, int $buildId): array;

    /**
     * 获取机构指定用户基本信息及所属部门信息.
     */
    public function getOrgUserById(int $orgId, int $uid): array;

    /**
     * 根据指定部门ID获取机构部门信息.
     */
    public function getOrgDepartmentByIds(int $orgId, array $departmentIds, string $labelType = 'one'): array;

    /**
     * 通过用户名称搜索指定机构用户列表.
     */
    public function getOrgUserList(int $orgId, string $name = ''): array;

    /**
     * 获取当前指定角色的权限.
     */
    public function getRoleRbacList(int $roleId, int $orgId, int $userId): array;

    /**
     * 获取菜单名称和权限名称.
     */
    public function getMenuAuthName(string $adminModule): array;
}
