<?php

namespace KikCMS\Command\App;

use KikCMS\Domain\App\Development\Cert\AppCertService;
use KikCMS\Domain\App\Development\Docker\DockerService;
use KikCMS\Kernel;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpKernel\KernelInterface;

#[AsCommand(
    name: 'kikcms:app:up',
    description: 'Launch development environment for this app',
)]
class UpCommand extends Command
{
    public function __construct(readonly int $id, readonly string $name, readonly int $portBase,
        readonly DockerService $dockerService, readonly AppCertService $certService, readonly KernelInterface $kernel)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $port       = $this->portBase + $this->id;
        $dockerFile = $this->kernel->getAppDir(Kernel::FILE_DOCKER_COMPOSE_SITE);
        $adminDir   = $this->kernel->getAppDir(Kernel::DIR_ADMIN);

        $io = new SymfonyStyle($input, $output);

        if ( ! $this->certService->certsAreInPlace($this->name)) {
            $this->certService->showCertWarning($io, $this->name);
        }

        if( ! is_dir($adminDir)) {
            $this->adminService->update($adminDir, $io);
        }

        return $this->dockerService->up($dockerFile, $this->name, $port, $io);
    }
}