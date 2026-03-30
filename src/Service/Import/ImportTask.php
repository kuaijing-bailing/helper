<?php

declare(strict_types=1);
/**
 * This file is part of Kuaijing Bailing.
 *
 * @link     https://www.kuaijingai.com
 * @document https://help.kuaijingai.com
 * @contact  www.kuaijingai.com 7*12 9:00-21:00
 */
namespace Bailing\Service\Import;

use Bailing\Constants\I18n\Common\CommonI18n;
use Bailing\Constants\I18n\Import\ImportI18n;
use Bailing\Helper\Intl\DateTimeHelper;
use Bailing\Helper\Intl\I18nHelper;
use Bailing\JsonRpc\Message\NoticeServiceInterface;
use Bailing\Model\BailingDataImportMainTask;
use Bailing\Model\BailingDataImportSubTask;
use Bailing\Office\Collection;

class ImportTask
{
    public int $errorNum = 0;

    public int $successNum = 0;

    public function addSubRecord(int $mainTaskId, string $taskId, array $data)
    {
        $model = new BailingDataImportSubTask();
        $model->task_id = $mainTaskId;
        $model->sub_task_id = $taskId;
        $model->data = $data;
        $model->save();

        return $model;
    }

    public function successAdd(int $num = 1): void
    {
        $this->successNum += $num;
    }

    public function errorAdd(int $num = 1): void
    {
        $this->errorNum += $num;
    }

    /**
     * 更新失败记录.
     */
    public function updateErrorRecord(BailingDataImportSubTask $model, array $record = []): void
    {
        $model->error_data = $record;
        $model->status = 2;
        $model->save();

        $this->errorAdd();
    }

    /**
     * 更新成功记录.
     */
    public function updateSuccessRecord(BailingDataImportSubTask $model, array $record = []): void
    {
        $model->error_data = $record;
        $model->status = 1;
        $model->save();

        $this->successAdd();
    }

    /**
     * 判断是否导入完毕.
     */
    public function isImportOver(int $mainTaskId, int $successNum, int $errorNum, string $redisKey)
    {
        return rateWait($redisKey, function () use ($mainTaskId, $successNum, $errorNum) {
            $mainTaskInfo = BailingDataImportMainTask::query()->where('id', $mainTaskId)->first();
            $mainTaskInfo->success_num += $successNum;
            $mainTaskInfo->error_num += $errorNum;
            $mainTaskInfo->save();

            // 子任务成功和失败的总数和总条数是否一致，且子任务没有待处理的
            if ($mainTaskInfo->data_total <= $mainTaskInfo->success_num + $mainTaskInfo->error_num) {
                return true;
            }
            return false;
        }, 100000);
    }

    /**
     * 发送导入结果通知.
     *
     * @throws \Exception
     */
    public function sendImportResult(string $dtoClass, int $mainTaskId, array $checkedBuild, array $extraInfo = []): void
    {
        $mainTaskInfo = BailingDataImportMainTask::query()->where('id', $mainTaskId)->first();
        $orgId = $mainTaskInfo->org_id;
        try {
            $operateId = $mainTaskInfo->created_uid;
            $errorData = BailingDataImportSubTask::query()->where('task_id', $mainTaskInfo->id)->where('status', 2)->pluck('error_data')->toArray();

            $fileResult = '';
            if (count($errorData) > 0) {
                $fileName = 'import_error_' . date('Ymd-His') . '.xlsx';
                try {
                    $fileResult = (new Collection())->export($dtoClass, $fileName, $errorData, $extraInfo, false, $orgId, ['check_build' => $checkedBuild, 'out_type' => 'file']);
                } catch (\Exception $e) {
                    throw new \Exception($e->getMessage());
                }
            }

            $contentArr = [
                [
                    'title' => ImportI18n::FILE_NAME->genI18nTxt(),
                    'content' => $mainTaskInfo->file_name,
                ],
                [
                    'title' => ImportI18n::IMPORT_TIME->genI18nTxt(),
                    'content' => DateTimeHelper::getFormatDateTime($mainTaskInfo->created_at->timestamp),
                ],
                [
                    'title' => ImportI18n::IMPORT_FAILED_COUNT->genI18nTxt(),
                    'content' => $mainTaskInfo->error_num,
                ],
                [
                    'title' => ImportI18n::IMPORT_SUCCESS_COUNT->genI18nTxt(),
                    'content' => $mainTaskInfo->success_num,
                ],
            ];

            try {
                $userLang = I18nHelper::getUserNowLang($operateId);
                container()->get(NoticeServiceInterface::class)->addNotice(
                    [
                        'orgId' => $orgId,
                        'uidArr' => [$operateId],
                        'catLabel' => 'system',
                        'title' => ImportI18n::IMPORT_RESULT_NOTIFICATION->genI18nTxt(),
                        'content' => $contentArr,
                        'remark' => CommonI18n::IMPORT_REMARK->genI18nTxt(returnNowLang: true, language: $userLang),
                        'link' => $fileResult . '?type=bigData',
                        'extra' => [],
                    ]
                );
            } catch (\Exception $e) {
                throw new \Exception($e->getMessage());
            }
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }
}
