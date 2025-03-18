<?php

declare(strict_types=1);
/**
 * This file is part of Kuaijing Bailing.
 *
 * @link     https://www.kuaijingai.com
 * @document https://help.kuaijingai.com
 * @contact  www.kuaijingai.com 7*12 9:00-21:00
 */
namespace Bailing\Listener;

use Bailing\Helper\TranslationHelper;
use Hyperf\Codec\Json;
use Hyperf\Context\Context;
use Hyperf\Database\Model\Events\Saved;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;
use Psr\Http\Message\ServerRequestInterface;

#[Listener]
class TranslationSavedListener implements ListenerInterface
{
    public function listen(): array
    {
        return [
            Saved::class,
        ];
    }

    public function process(object $event): void
    {
        $model = $event->getModel();

        // 保存时去除国际化字段
        if (cfg('open_internationalize')) {
            $redis = redis();
            $table = $model->getTable();
            $tableI18nConfig = config('translation.i18n_table.' . $table);
            if (! empty($tableI18nConfig)) {
                $request = Context::get(ServerRequestInterface::class);
                $relationField = $tableI18nConfig['relation'] ?? 'id';

                // 一般在于内网交互、系统启动时，不存在请求头，初始化中文的内容，不覆盖（两个IP参数的判断是用于jsonRpc请求）
                if (empty($request) || (empty(request()->getHeaderLine('x-forwarded-for')) && empty(request()->getHeaderLine('x-real-ip')))) {
                    foreach ($tableI18nConfig['i18n'] as $item) {
                        if (! empty($model->{$item})) {
                            // 优先判断redis中有没有，可以事先埋入(表名、缓存标识辅助字段、值的md5)
                            $redisKey = sprintf('i18n:%s%s:%s', $table, ! empty($tableI18nConfig['saveUniqueField']) ? ':' . $model->{$tableI18nConfig['saveUniqueField']} : '', md5($model->{$item}));
                            $cacheI18nValue = $redis->get($redisKey);
                            if (! empty($cacheI18nValue)) {
                                $i18nValue = Json::decode($cacheI18nValue);
                                $redis->del($redisKey);
                            } else {
                                $i18nValue = ['zh_cn' => $model->{$item}];
                            }
                            TranslationHelper::saveTranslation(! empty($model->org_id) ? $model->org_id : 0, $table . '_' . $item, $model->{$relationField}, $i18nValue, false);
                        }
                    }
                    return;
                }

                // 用户端编辑保存
                $requestData = request()->all();
                if (empty($requestData)) {
                    return;
                }

                foreach ($tableI18nConfig['i18n'] as $item) {
                    $tmpKey = 'i18n_' . $item;
                    if (! empty($requestData[$tmpKey])) {
                        TranslationHelper::saveTranslation(! empty($model->org_id) ? $model->org_id : 0, $table . '_' . $item, $model->{$relationField}, $requestData[$tmpKey], true, true);
                    }
                }
            }
        }
    }
}
