<?php

namespace KikCMS\Command\Development;

use KikCMS\Domain\App\Development\Docker\DockerService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

#[AsCommand(
    name: 'dev:attach',
    description: 'Get inside the docker container',
)]
class AttachCommand extends Command
{
    private string $name;
    private DockerService $dockerService;

    public function __construct(string $name, DockerService $dockerService)
    {
        parent::__construct();

        $this->name          = $name;
        $this->dockerService = $dockerService;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $container = $this->dockerService->getContainerName($this->name);

        $process = new Process(['docker', 'exec', '-it', $container, '/bin/bash']);
        $process->setTty(Process::isTtySupported());
        $process->setTimeout(null);
        $process->run();

        return Command::SUCCESS;
    }
}