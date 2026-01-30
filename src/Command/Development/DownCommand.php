<?php

namespace KikCMS\Command\Development;

use KikCMS\Domain\App\Development\Docker\DockerService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'dev:down',
    description: 'Launch development environment for this app',
)]
class DownCommand extends Command
{
    private int $id;
    private int $portBase;
    private string $name;
    private DockerService $dockerService;

    public function __construct(int $id, string $name, int $portBase, DockerService $dockerService)
    {
        parent::__construct();

        $this->id            = $id;
        $this->name          = $name;
        $this->portBase      = $portBase;
        $this->dockerService = $dockerService;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->dockerService->down($this->name, $this->portBase + $this->id);
        return Command::SUCCESS;
    }
}