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

use Bailing\Annotation\RateRequest;
use Bailing\Constants\Code\Common\CommonCode;
use Bailing\Helper\ApiHelper;
use Bailing\Middleware\OrgMiddleware;
use Bailing\Model\BailingExtraFields;
use Hyperf\Database\Model\Builder;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\DeleteMapping;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\PostMapping;
use Hyperf\HttpServer\Annotation\PutMapping;

#[Controller]
#[Middleware(OrgMiddleware::class)]
class ExtraFieldsController
{
    #[PostMapping(path: '/common/extra/field/query')]
    public function list(): array
    {
        $post = request()->all();
        $pageSize = request()->input('pageSize', 20);
        $nowAdmin = contextGet('nowUser');

        // 默认为ID倒序
        if (empty($post['sorts'])) {
            $post['sorts'] = [
                [
                    'field' => 'sort',
                    'asc' => false,
                ],
                [
                    'field' => 'id',
                    'asc' => false,
                ],
            ];
        }
        $list = buildFormSearchQuery(BailingExtraFields::query()->where(['org_id' => $nowAdmin->org_id]), $post['filters'], $post['sorts'])->paginate((int) $pageSize)->toArray();

        return ApiHelper::genSuccessData(
            genListData(
                $list,
                [
                    'dict_menu_alias' => 'orgExtraFieldType',
                ]
            )
        );
    }

    #[GetMapping(path: '/common/extra/field/all')]
    public function all(): array
    {
        $post = request()->all();
        $nowAdmin = contextGet('nowUser');

        $list = BailingExtraFields::query()->where(['org_id' => $nowAdmin->org_id, 'alias' => $post['alias']])->orderByDesc('sort')->orderByDesc('id')->get()->toArray();

        return ApiHelper::genSuccessData(['list' => $list]);
    }

    #[PostMapping(path: '/common/extra/field')]
    #[RateRequest]
    public function add(): array
    {
        return self::editHandle();
    }

    #[PutMapping(path: '/common/extra/field/{id:\d+}')]
    #[RateRequest(rateKey: 'id')]
    public function edit(?int $id): array
    {
        return self::editHandle($id);
    }

    #[GetMapping(path: '/common/extra/field/{id:\d+}')]
    public function detail(int $id): array
    {
        $nowAdmin = contextGet('nowUser');

        $detail = BailingExtraFields::query()->where(['id' => $id, 'org_id' => $nowAdmin->org_id])->first();
        if (empty($detail)) {
            return ApiHelper::genErrorDataEmpty();
        }
        $detailArr = $detail->toArray();

        return ApiHelper::genSuccessData(['detail' => $detailArr]);
    }

    #[DeleteMapping(path: '/common/extra/field/{id:\d+}')]
    #[RateRequest(rateKey: 'id')]
    public function delete(int $id): array
    {
        $nowAdmin = contextGet('nowUser');

        $detail = BailingExtraFields::query()->where(['id' => $id, 'org_id' => $nowAdmin->org_id])->first();
        if (empty($detail)) {
            return ApiHelper::genErrorDataEmpty();
        }

        try {
            if (! $detail->delete()) {
                return ApiHelper::genErrorData(CommonCode::OPERATION_FAILED);
            }
        } catch (\Exception $e) {
            stdLog()->error('BailingExtraFields delete error：' . $e->getMessage());
            return ApiHelper::genErrorData(CommonCode::OPERATION_FAILED);
        }

        return ApiHelper::genSuccessData(['id' => $detail->id], CommonCode::OPERATION_SUCCESS);
    }

    private function editHandle(?int $id = 0): array
    {
        $post = request()->all();
        $nowAdmin = contextGet('nowUser');

        $isExist = BailingExtraFields::query()->where('org_id', $nowAdmin->org_id)
            ->where('fields_name', $post['fields_name'])
            ->when(! empty($id), function (Builder $query) use ($id) {
                $query->where('id', '!=', $id);
            })
            ->exists();
        if ($isExist) {
            return ApiHelper::genErrorData(CommonCode::NAME_REPEAT);
        }

        if (! empty($id)) {
            $model = BailingExtraFields::query()->where(['id' => $id, 'org_id' => $nowAdmin->org_id])->first();
            if (empty($model)) {
                return ApiHelper::genErrorDataEmpty();
            }
            $model->updated_uid = (int) $nowAdmin->id;
            $model->updated_name = (string) $nowAdmin->name;
        } else {
            $model = new BailingExtraFields();
            $model->org_id = $nowAdmin->org_id;
            $model->key = md5(explode(' ', microtime())[0] . mt_rand(1, 1000000));
            $model->created_uid = (int) $nowAdmin->id;
            $model->created_name = (string) $nowAdmin->name;
        }
        isset($post['alias']) && $model->alias = (string) $post['alias'];
        isset($post['fields_name']) && $model->fields_name = (string) $post['fields_name'];
        isset($post['fields_type']) && $model->fields_type = (string) $post['fields_type'];
        isset($post['fill']) && $model->fill = (int) $post['fill'];
        isset($post['default_value']) && $model->default_value = (string) $post['default_value'];
        isset($post['min_length']) && $model->min_length = (int) $post['min_length'];
        isset($post['max_length']) && $model->max_length = (int) $post['max_length'];
        isset($post['placeholder']) && $model->placeholder = (string) $post['placeholder'];
        isset($post['option_value']) && $model->option_value = (array) $post['option_value'];
        isset($post['date_type']) && $model->date_type = (int) $post['date_type'];
        isset($post['option_max']) && $model->option_max = (int) $post['option_max'];
        isset($post['file_type']) && $model->file_type = (string) $post['file_type'];
        isset($post['sort']) && $model->sort = (int) $post['sort'];

        if (! $model->save()) {
            return ApiHelper::genErrorData(CommonCode::SAVE_FAILED);
        }

        return ApiHelper::genSuccessData(['id' => $model->id], CommonCode::SAVE_SUCCESS);
    }
}
