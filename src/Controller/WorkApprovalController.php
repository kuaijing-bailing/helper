<?php

declare(strict_types=1);
/**
 * This file is part of Kuaijing Bailing.
 *
 * @link     https://www.kuaijingai.com
 * @document https://help.kuaijingai.com
 * @contact  www.kuaijingai.com 7*12 9:00-21:00
 */

namespace Bailing\Controller;

use Bailing\Constants\Code\Common\CommonCode;
use Bailing\Helper\ApiHelper;
use Bailing\Helper\Approval\ApprovalProcessHelper;
use Bailing\Helper\OrgConfigHelper;
use Bailing\Helper\Webhook\WebhookInvokeHelper;
use Bailing\Middleware\OrgMiddleware;
use Bailing\Model\BailingApprovalCategory;
use Bailing\Model\BailingApprovalModule;
use Hyperf\Codec\Json;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\PostMapping;

#[Controller]
class WorkApprovalController
{
    #[PostMapping(path: '/work_approval/approval_callback')]
    public function approvalCallback(): array
    {
        $dataEncrypt = request()->input('data');
        if (empty($dataEncrypt)) {
            return ApiHelper::genErrorData('Error request，parameter[data] not found');
        }

        try {
            $dataArr = WebhookInvokeHelper::parseServiceMsg($dataEncrypt);
        } catch (\Exception $e) {
            logger()->info('error', [$e->getMessage()]);
            return ApiHelper::genErrorData('Error request，parameter[data] error：' . $e->getMessage());
        }
        logger()->info('审批回调的线索', [$dataArr]);

        $config = config('approval');
        if (! empty($config)) {
            return container()->get($config[$dataArr['third_label']])->approvecallBack($dataArr);
        }
        logger()->info('config没有配置审批回调文件');
        return ApiHelper::genSuccessData([]);
    }

    #[GetMapping(path: '/category/process/list')]
    #[Middleware(OrgMiddleware::class)]
    public function approvalProcessList(): array
    {
        $get = request()->all();
        $alias = $get['alias'] ?? '';
        if (empty($alias)) {
            return ApiHelper::genErrorData('param[alias] error');
        }
        $nowAdmin = contextGet('nowUser');

        $approvalResult = ApprovalProcessHelper::getApprovalModuleList($alias);
        $list = [];
        if (ApiHelper::checkDataOk($approvalResult)) {
            $list = $approvalResult['data']['list'];
        }
        $result = [];
        $isInit = true;
        foreach ($list as $item) {
            // 是否已经初始化过
            $initConfig = OrgConfigHelper::getConfig($nowAdmin->org_id, 'approval_' . $item['alias']);
            if (! empty($initConfig)) {
                $initConfig = Json::decode($initConfig);
                if (! empty($initConfig['id'])) {
                    $temp['id'] = $initConfig['id'];
                } else {
                    $isInit = false;
                }
            } else {
                $isInit = false;
            }
            $temp['name'] = $item['name'];
            $temp['i18n_name'] = [
                'value' => $item['name'],
                'i18n_value' => $item['i18n_name'],
            ];
            $temp['alias'] = $item['alias'];
            $temp['icon'] = domain() . '/public_web/static/images' . $item['icon'];
            $result[] = $temp;
        }

        return ApiHelper::genSuccessData(['list' => $result, 'is_init' => $isInit]);
    }

    #[PostMapping(path: '/approval/process')]
    #[Middleware(OrgMiddleware::class)]
    public function approvalProcess(): array
    {
        $post = request()->all();
        $aliasArr = $post['alias'];
        if (empty($aliasArr)) {
            return ApiHelper::genErrorData(CommonCode::PARAM_ERROR);
        }
        $nowAdmin = contextGet('nowUser');

        foreach ($aliasArr as $alias) {
            $config = OrgConfigHelper::getConfig($nowAdmin->org_id, 'approval_' . $alias);
            if (! empty($config)) {
                $config = Json::decode($config);
            }
            if (empty($config['id'])) {
                $approvalModule = BailingApprovalModule::query()->where('alias', $alias)->first();
                if (empty($approvalModule)) {
                    return ApiHelper::genErrorData('approval module not found');
                }
                if (! empty($approvalModule->sub_cat_alias)) {
                    $catApprovalList = BailingApprovalModule::query()->where('sub_cat_alias', $approvalModule->sub_cat_alias)->get()->toArray();
                } else {
                    $catApprovalList = BailingApprovalModule::query()->where('source_type', $approvalModule->source_type)->get()->toArray();
                }
                $initData = [];
                foreach ($catApprovalList as $list) {
                    $firstCategory = BailingApprovalCategory::query()->where('alias', $list['source_type'])->first();
                    if (! $firstCategory) {
                        return ApiHelper::genErrorData('category is not exist,please add category first');
                    }
                    $secondCategory = BailingApprovalCategory::query()->where('alias', $list['sub_cat_alias'])->first();
                    $temp = [
                        'alias' => $list['alias'],
                        'name' => $list['name'],
                        'i18n_name' => $list['i18n_name'],
                        'desc' => $list['desc'],
                        'i18n_desc' => $list['i18n_desc'],
                        'approval_type' => $list['approval_type'],
                        'source_type' => $firstCategory->alias,
                        'source_txt' => $firstCategory->source_txt,
                        'i18n_source_txt' => $firstCategory->i18n_source_txt,
                        'cat_type' => $firstCategory->name,
                        'i18n_cat_type' => $firstCategory->i18n_name,
                        'sub_cat_type' => $secondCategory ? $secondCategory->name : '',
                        'i18n_sub_cat_type' => $secondCategory ? $secondCategory->i18n_name : '',
                        'sub_cat_alias' => $secondCategory ? $secondCategory->alias : '',
                        'icon' => $list['icon'],
                        'form' => $list['form'],
                        'start_user_type' => $list['start_user_type'], // 2所有人不能发起,0所有人可以发起
                        'process' => [],
                        'first_cate_sort' => ! empty($firstCategory->cate_sort) ? $firstCategory->cate_sort : 0,
                        'second_cate_sort' => $secondCategory ? $secondCategory->cate_sort : 0,
                    ];
                    $initData[] = $temp;
                }
                // 初始化流程
                ApprovalProcessHelper::initProcess($nowAdmin->org_id, $nowAdmin->id, $alias, $initData);
            }
        }
        return ApiHelper::genSuccessData([], CommonCode::OPERATION_SUCCESS);
    }
}
