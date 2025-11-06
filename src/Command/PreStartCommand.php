<?php

declare(strict_types=1);
/**
 * This file is part of Kuaijing Bailing.
 *
 * @link     https://www.kuaijingai.com
 * @document https://help.kuaijingai.com
 * @contact  www.kuaijingai.com 7*12 9:00-21:00
 */
namespace Bailing\Command;

use Hyperf\Command\Annotation\Command;
use Hyperf\Command\Command as HyperfCommand;

#[Command]
class PreStartCommand extends HyperfCommand
{
    public function __construct()
    {
        parent::__construct('preStart');
    }

    public function handle()
    {
        $this->call('migrate', ['--path' => './vendor/bailing/helper/src/migrations']);
        $this->call('migrate');
    }
}