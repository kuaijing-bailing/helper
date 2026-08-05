<?php

declare(strict_types=1);
/**
 * This file is part of Kuaijing Bailing.
 *
 * @link     https://www.kuaijingai.com
 * @document https://help.kuaijingai.com
 * @contact  www.kuaijingai.com 7*12 9:00-21:00
 */

namespace Bailing\Model;

use Carbon\Carbon;

/**
 * @property int $id
 * @property int $org_id
 * @property string $index
 * @property string $name
 * @property string $value
 * @property null|string $remark
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class BailingOrgConfig extends Model
{
    /**
     * The table associated with the model.
     */
    protected ?string $table = 'bailing_org_config';

    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = [];

    /**
     * The attributes that should be cast to native types.
     */
    protected array $casts = ['id' => 'integer', 'org_id' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime'];
}
