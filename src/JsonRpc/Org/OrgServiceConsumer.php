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

use Bailing\Helper\ApiHelper;
use Hyperf\RpcClient\AbstractServiceClient;

class OrgServiceConsumer extends AbstractServiceClient implements OrgServiceInterface
{
    /**
     * 定义对应服务提供者的服务名称.
     */
    protected string $serviceName = 'OrgService';

    /**
     * 定义对应服务提供者的服务协议.
     */
    protected string $protocol = 'jsonrpc-http';

    /**
     * 添加机构和项目绑定关系.
     * @param array $data 二维数组 [['org_id' => '', 'village_id' => ''], ['org_id' => '', 'village_id' => '']]
     */
    public function addOrgVillageRelation(array $data): array
    {
        try {
            return $this->__request(__FUNCTION__, compact('data'));
        } catch (\Exception $exception) {
            return ApiHelper::genServiceErrorData($this->serviceName, $exception);
        }
    }

    /**
     * 获取用户管理的机构.
     * @param int $uid 用户ID
     */
    public function getOrgByAdminUid(int $uid): array
    {
        try {
            return $this->__request(__FUNCTION__, compact('uid'));
        } catch (\Exception $exception) {
            return ApiHelper::genServiceErrorData($this->serviceName, $exception);
        }
    }

    /**
     * 通过机构ID获取项目的绑定关系.
     * @param int $orgId 机构ID
     */
    public function getVillageRelationByOrgId(int $orgId): array
    {
        try {
            return $this->__request(__FUNCTION__, compact('orgId'));
        } catch (\Exception $exception) {
            return ApiHelper::genServiceErrorData($this->serviceName, $exception);
        }
    }

    /**
     * 根据ID数组获取房源标签列表.
     */
    public function getTagHouseList(array $tagIdArr, int $orgId = 0, int $userShow = -1): array
    {
        try {
            return $this->__request(__FUNCTION__, compact('tagIdArr', 'orgId', 'userShow'));
        } catch (\Exception $exception) {
            return ApiHelper::genServiceErrorData($this->serviceName, $exception);
        }
    }

    /**
     * 根据ID数组获取房源标签列表.
     */
    public function getTagBuildList(array $tagIdArr, int $orgId = 0, int $userShow = -1): array
    {
        try {
            return $this->__request(__FUNCTION__, compact('tagIdArr', 'orgId', 'userShow'));
        } catch (\Exception $exception) {
            return ApiHelper::genServiceErrorData($this->serviceName, $exception);
        }
    }

    /**
     * 根据ID数组获取项目标签列表.
     */
    public function getTagVillageList(array $tagIdArr, int $orgId = 0, int $userShow = -1): array
    {
        try {
            return $this->__request(__FUNCTION__, compact('tagIdArr', 'orgId', 'userShow'));
        } catch (\Exception $exception) {
            return ApiHelper::genServiceErrorData($this->serviceName, $exception);
        }
    }

    /**
     * 楼宇绑定收支账户.
     */
    public function buildBindAccount(array $accountIds, int $buildId, string $buildName): array
    {
        try {
            return $this->__request(__FUNCTION__, compact('accountIds', 'buildId', 'buildName'));
        } catch (\Exception $exception) {
            return ApiHelper::genServiceErrorData($this->serviceName, $exception);
        }
    }

    /**
     * 根据ID 获取用户在机构内的信息.
     */
    public function getUserByUserIdArr(array $userIdArr, int $orgId = 0): array
    {
        try {
            return $this->__request(__FUNCTION__, compact('userIdArr', 'orgId'));
        } catch (\Exception $exception) {
            return ApiHelper::genServiceErrorData($this->serviceName, $exception);
        }
    }

    /**
     * 根据ID 获取机构的基本信息.
     */
    public function getOrgByOrgIdArr(array|int $orgIdArr): array
    {
        try {
            return $this->__request(__FUNCTION__, compact('orgIdArr'));
        } catch (\Exception $exception) {
            return ApiHelper::genServiceErrorData($this->serviceName, $exception);
        }
    }

    /**
     * 根据域名前缀获取机构的基本信息.
     */
    public function getOrgByDomainPrefix(string $domainPrefix): array
    {
        try {
            return $this->__request(__FUNCTION__, compact('domainPrefix'));
        } catch (\Exception $exception) {
            return ApiHelper::genServiceErrorData($this->serviceName, $exception);
        }
    }

    /**
     * 根据ID 获取租客基本信息列表.
     */
    public function getOwnerByIdArr(array $idArr, int $orgId = 0): array
    {
        try {
            return $this->__request(__FUNCTION__, compact('idArr', 'orgId'));
        } catch (\Exception $exception) {
            return ApiHelper::genServiceErrorData($this->serviceName, $exception);
        }
    }

    /**
     * 根据指定owner_id获取合同关联房间信息.
     */
    public function getRoomIdByOwner(int $ownerId): array
    {
        try {
            return $this->__request(__FUNCTION__, compact('ownerId'));
        } catch (\Exception $exception) {
            return ApiHelper::genServiceErrorData($this->serviceName, $exception);
        }
    }

    /**
     * 根据指定owner_id/owner指定条件获取机构账单信息.
     */
    public function getBillListByOwner(int $ownerId, array $where): array
    {
        try {
            return $this->__request(__FUNCTION__, compact('ownerId', 'where'));
        } catch (\Exception $exception) {
            return ApiHelper::genServiceErrorData($this->serviceName, $exception);
        }
    }

    /**
     * 根据指定账单ID获取账单详情(含子账单明细).
     */
    public function getBillDetailById(int $billId, array $where): array
    {
        try {
            return $this->__request(__FUNCTION__, compact('billId', 'where'));
        } catch (\Exception $exception) {
            return ApiHelper::genServiceErrorData($this->serviceName, $exception);
        }
    }

    /**
     * 根据指定uid获取机构绑定的最新登录的用户信息.
     */
    public function getOrgUserDataByUid(int $userId, int $orgId = 0, array $fields = []): array
    {
        try {
            return $this->__request(__FUNCTION__, compact('userId', 'orgId', 'fields'));
        } catch (\Exception $exception) {
            return ApiHelper::genServiceErrorData($this->serviceName, $exception);
        }
    }

    /**
     * 根据指定uid获取机构绑定的楼层管理的权限.
     */
    public function getOrgUserManageLayersByUid(int $userId, int $orgId, int $buildId = 0): array
    {
        try {
            return $this->__request(__FUNCTION__, compact('userId', 'orgId', 'buildId'));
        } catch (\Exception $exception) {
            return ApiHelper::genServiceErrorData($this->serviceName, $exception);
        }
    }

    /**
     * 获取部门和人员列表.
     */
    public function getOrgDepartmentUser(array $departIdArr, array $userIdArr, int $orgId): array
    {
        try {
            return $this->__request(__FUNCTION__, compact('departIdArr', 'userIdArr', 'orgId'));
        } catch (\Exception $exception) {
            return ApiHelper::genServiceErrorData($this->serviceName, $exception);
        }
    }

    /**
     * 通过指定楼宇id获取租客列表.
     */
    public function getOwnerByBuildId(int $buildId, int $overdueDay = 0): array
    {
        try {
            return $this->__request(__FUNCTION__, compact('buildId', 'overdueDay'));
        } catch (\Exception $exception) {
            return ApiHelper::genServiceErrorData($this->serviceName, $exception);
        }
    }

    /**
     * 通过指定项目id获取租客列表.
     */
    public function getOwnerByVillageId(int $villageId, int $overdueDay = 0): array
    {
        try {
            return $this->__request(__FUNCTION__, compact('villageId', 'overdueDay'));
        } catch (\Exception $exception) {
            return ApiHelper::genServiceErrorData($this->serviceName, $exception);
        }
    }

    /**
     * 通过租客ID获取其所有合同对应的项目、楼栋ID.
     */
    public function getVillageBuildByOwner(int $ownerId, int $overdueDay = 0): array
    {
        try {
            return $this->__request(__FUNCTION__, compact('ownerId', 'overdueDay'));
        } catch (\Exception $exception) {
            return ApiHelper::genServiceErrorData($this->serviceName, $exception);
        }
    }

    /**
     * 水电抄表同步账单数据至org服务接管
     */
    public function energyOrderHandel(int $orgId, array $billArr): array
    {
        try {
            return $this->__request(__FUNCTION__, compact('orgId', 'billArr'));
        } catch (\Exception $exception) {
            return ApiHelper::genServiceErrorData($this->serviceName, $exception);
        }
    }

    /**
     * 根据指定房间id获取合同列表 return: 租客名称、租赁面积、物业面积、起止日期、状态
     */
    public function getContractByRoom(int $orgId, int $roomId): array
    {
        try {
            return $this->__request(__FUNCTION__, compact('orgId', 'roomId'));
        } catch (\Exception $exception) {
            return ApiHelper::genServiceErrorData($this->serviceName, $exception);
        }
    }

    /**
     * 根据指定项目id获取项目下所有楼宇在租房间的租金收益.
     */
    public function getVillageRentalIncome(int $orgId, int $villageId, int $buildId): array
    {
        try {
            return $this->__request(__FUNCTION__, compact('orgId', 'villageId', 'buildId'));
        } catch (\Exception $exception) {
            return ApiHelper::genServiceErrorData($this->serviceName, $exception);
        }
    }

    /**
     * 获取机构指定用户基本信息及所属部门信息.
     */
    public function getOrgUserById(int $orgId, int $uid): array
    {
        try {
            return $this->__request(__FUNCTION__, compact('orgId', 'uid'));
        } catch (\Exception $exception) {
            return ApiHelper::genServiceErrorData($this->serviceName, $exception);
        }
    }

    /**
     * 根据指定部门ID获取机构部门信息.
     */
    public function getOrgDepartmentByIds(int $orgId, array $departmentIds, string $labelType = 'one'): array
    {
        try {
            return $this->__request(__FUNCTION__, compact('orgId', 'departmentIds', 'labelType'));
        } catch (\Exception $exception) {
            return ApiHelper::genServiceErrorData($this->serviceName, $exception);
        }
    }

    /**
     * 通过用户名称搜索指定机构用户列表.
     */
    public function getOrgUserList(int $orgId, string $name = ''): array
    {
        try {
            return $this->__request(__FUNCTION__, compact('orgId', 'name'));
        } catch (\Exception $exception) {
            return ApiHelper::genServiceErrorData($this->serviceName, $exception);
        }
    }

    /**
     * 获取当前指定角色的权限.
     */
    public function getRoleRbacList(int $roleId, int $orgId, int $userId): array
    {
        try {
            return $this->__request(__FUNCTION__, compact('roleId', 'orgId', 'userId'));
        } catch (\Exception $exception) {
            return ApiHelper::genServiceErrorData($this->serviceName, $exception);
        }
    }

    /**
     * 查询没有权限时的菜单名称和权限名称.
     */
    public function getMenuAuthName(string $adminModule): array
    {
        try {
            return $this->__request(__FUNCTION__, compact('adminModule'));
        } catch (\Exception $exception) {
            return ApiHelper::genServiceErrorData($this->serviceName, $exception);
        }
    }
}
