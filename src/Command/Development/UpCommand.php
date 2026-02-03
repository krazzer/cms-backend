<?php

namespace KikCMS\Command\Development;

use KikCMS\Domain\App\Development\Cert\CertService;
use KikCMS\Domain\App\Development\Docker\DockerService;
use KikCMS\Kernel;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpKernel\KernelInterface;

#[AsCommand(
    name: 'dev:up',
    description: 'Launch development environment for this app',
)]
class UpCommand extends Command
{
    private int $id;
    private int $portBase;
    private string $name;

    public function __construct(int $id, string $name, int $portBase, readonly DockerService $dockerService,
        readonly CertService $certService, readonly KernelInterface $kernel)
    {
        parent::__construct();

        $this->id       = $id;
        $this->name     = $name;
        $this->portBase = $portBase;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $port = $this->portBase + $this->id;

        $io = new SymfonyStyle($input, $output);

        if ( ! $this->certService->certsAreInPlace($this->name)) {
            $this->showCertWarning($io);
        }

        if ($this->dockerService->isRunning($this->name)) {
            $io->success("Docker container $this->name is already running");
        } else {
            $this->dockerService->up($this->name, $this->portBase + $this->id);
        }

        $uri           = 'https://localhost:' . $port;
        $uriWithDomain = 'https://' . $this->name . '.test:' . $port;

        $io->success("You can now visit the website at:\n$uri\nor, if the domain is pointing to localhost:\n" .
            $uriWithDomain);

        return Command::SUCCESS;
    }

    private function showCertWarning(SymfonyStyle $io): void
    {
        $location = $this->kernel->getAppDir(Kernel::DIR_CERTS . DIRECTORY_SEPARATOR);
        $command  = "mkcert -cert-file cert.crt -key-file cert.key localhost $this->name.test";
        $io->warning("Certificate files are missing, now using fake files. Generate them using:\n$command, " .
            "overwrite the files in: $location, and then restart the container.");
    }
}