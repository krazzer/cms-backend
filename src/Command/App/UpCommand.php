<?php

namespace KikCMS\Command\App;

use KikCMS\Domain\App\Admin\AdminService;
use KikCMS\Domain\App\Development\Cert\AppCertService;
use KikCMS\Domain\App\Development\Docker\DockerService;
use KikCMS\Domain\App\Path\PathConfig;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[AsCommand(
    name: 'kikcms:app:up',
    description: 'Launch development environment for this app',
)]
class UpCommand extends AppCommand
{
    public function __construct(
        #[Autowire('%app.id%')] readonly int $id,
        #[Autowire('%app.name%')] readonly string $name,
        #[Autowire('%app.portBase%')] readonly int $portBase,
        private readonly DockerService $dockerService,
        private readonly AppCertService $certService,
        private readonly AdminService $adminService)
    {
        parent::__construct();
    }

    protected function executeScoped(SymfonyStyle $io): int
    {
        $port       = $this->portBase + $this->id;
        $dockerFile = $this->kernel->getCmsDir(PathConfig::FILE_DOCKER_COMPOSE_SITE);
        $adminDir   = $this->kernel->getAppDir(PathConfig::DIR_PUBLIC . '/' . PathConfig::SUBDIR_ADMIN);

        if ( ! $this->certService->certsAreInPlace($this->name)) {
            $this->certService->showCertWarning($io, $this->name);
        }

        if ( ! is_dir($adminDir)) {
            $this->adminService->update($adminDir, $io);
        }

        return $this->dockerService->up($dockerFile, $this->name, $port, $io);
    }
}