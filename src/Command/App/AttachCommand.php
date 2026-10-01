<?php

namespace KikCMS\Command\App;

use KikCMS\Domain\App\Development\Docker\DockerService;
use KikCMS\Domain\App\Path\PathConfig;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[AsCommand(
    name: 'kikcms:app:attach',
    description: 'Get inside the docker container of this app',
)]
class AttachCommand extends AppCommand
{
    public function __construct(
        #[Autowire('%app.name%')] readonly string $name,
        private readonly DockerService $dockerService,
    )
    {
        parent::__construct();
    }

    protected function executeScoped(SymfonyStyle $io): int
    {
        $dockerFile = $this->kernel->getCmsDir(PathConfig::FILE_DOCKER_COMPOSE_SITE);

        return $this->dockerService->attach($dockerFile, $this->name);
    }
}