<?php

namespace KikCMS\Command\App;

use KikCMS\Domain\App\Development\Docker\DockerComposeService;
use KikCMS\Domain\App\Path\PathConfig;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[AsCommand(
    name: 'kikcms:app:down',
    description: 'Shut down development environment for this app',
)]
class DownCommand extends AppCommand
{
    public function __construct(
        #[Autowire('%app.id%')] readonly int $id,
        #[Autowire('%app.name%')] readonly string $name,
        #[Autowire('%app.portBase%')] readonly int $portBase,
        readonly DockerComposeService $dockerComposeService
    )
    {
        parent::__construct();
    }

    protected function executeScoped(SymfonyStyle $io): int
    {
        $dockerFile = $this->kernel->getCmsDir(PathConfig::FILE_DOCKER_COMPOSE_SITE);

        $this->dockerComposeService->down($dockerFile, $this->name, $this->portBase + $this->id);
        return Command::SUCCESS;
    }
}