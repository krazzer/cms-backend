<?php

namespace App;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    public function boot(): void
    {
        parent::boot();

        if (empty($_ENV['DEFAULT_EMAIL_FROM'])) {
            throw new \RuntimeException('Required DEFAULT_EMAIL_FROM variable is missing');
        }
    }
}
