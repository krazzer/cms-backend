<?php

namespace KikCMS\Command\App;

use KikCMS\Domain\App\Development\Docker\DockerComposeService;
use KikCMS\Kernel;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpKernel\KernelInterface;

#[AsCommand(
    name: 'kikcms:app:down',
    description: 'Shut down development environment for this app',
)]
class DownCommand extends Command
{
    public function __construct(readonly int $id, readonly string $name, readonly int $portBase,
        readonly DockerComposeService $dockerComposeService, readonly KernelInterface $kernel)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $dockerFile = $this->kernel->getCmsDir(Kernel::FILE_DOCKER_COMPOSE_SITE);

        $this->dockerComposeService->down($dockerFile, $this->name, $this->portBase + $this->id);
        return Command::SUCCESS;
    }
}