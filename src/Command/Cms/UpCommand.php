<?php

namespace KikCMS\Command\Cms;

use KikCMS\Domain\App\Admin\AdminService;
use KikCMS\Domain\App\Development\Cert\CmsCertService;
use KikCMS\Domain\App\Development\Docker\DockerService;
use KikCMS\Kernel;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpKernel\KernelInterface;

#[AsCommand(
    name: 'kikcms:cms:up',
    description: 'Launch development environment for the CMS (standalone)',
)]
class UpCommand extends Command
{
    public function __construct(readonly string $name, readonly int $port,
        private readonly DockerService $dockerService,
        private readonly CmsCertService $certService,
        private readonly KernelInterface $kernel,
        private readonly AdminService $adminService)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $dockerFile = $this->kernel->getCmsDir(Kernel::FILE_DOCKER_COMPOSE);
        $adminDir   = $this->kernel->getCmsDir(Kernel::DIR_ADMIN);

        if ( ! $this->certService->certsAreInPlace($this->name)) {
            $this->certService->showCertWarning($io, $this->name);
        }

        if ( ! is_dir($adminDir)) {
            $this->adminService->update($adminDir, $io);
        }

        return $this->dockerService->up($dockerFile, $this->name, $this->port, $io);
    }
}