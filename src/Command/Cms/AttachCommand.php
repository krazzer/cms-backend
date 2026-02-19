<?php

namespace KikCMS\Command\Cms;

use KikCMS\Domain\App\Development\Docker\DockerService;
use KikCMS\Kernel;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpKernel\KernelInterface;

#[AsCommand(
    name: 'kikcms:cms:attach',
    description: 'Get inside the docker container',
)]
class AttachCommand extends Command
{
    public function __construct(readonly string $name, private readonly KernelInterface $kernel,
        private readonly DockerService $dockerService)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $dockerFile = $this->kernel->getCmsDir(Kernel::FILE_DOCKER_COMPOSE);

        return $this->dockerService->attach($dockerFile, $this->name);
    }
}