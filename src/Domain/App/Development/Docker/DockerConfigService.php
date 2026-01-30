<?php

namespace KikCMS\Domain\App\Development\Docker;

use KikCMS\Kernel;
use Symfony\Component\HttpKernel\KernelInterface;

readonly class DockerConfigService
{
    public function __construct(
        private KernelInterface $kernel
    ) {}

    public function getDockerFile(): string
    {
        return $this->kernel->getCmsDir(Kernel::FILE_DOCKER_COMPOSE_SITE);
    }
}