<?php

namespace KikCMS\Command\Cms;

use KikCMS\Domain\App\Development\Docker\DockerComposeService;
use KikCMS\Kernel;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpKernel\KernelInterface;

#[AsCommand(
    name: 'kikcms:cms:down',
    description: 'Shut down development environment for the CMS (standalone)',
)]
class DownCommand extends Command
{
    public function __construct(readonly string $name, readonly int $port,
        readonly DockerComposeService $dockerComposeService, readonly KernelInterface $kernel)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $dockerFile = $this->kernel->getCmsDir(Kernel::FILE_DOCKER_COMPOSE);

        $this->dockerComposeService->down($dockerFile, $this->name, $this->port);

        return Command::SUCCESS;
    }
}