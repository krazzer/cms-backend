<?php

namespace KikCMS\Domain\App\Development\Docker;

use KikCMS\Kernel;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Process\Process;

readonly class DockerService
{
    public function __construct(private DockerComposeService $dockerComposeService, private KernelInterface $kernel) {}

    public function up(string $dockerFile, string $name, int $port, SymfonyStyle $io): int
    {
        $servicesDockerFile = $this->kernel->getCmsDir(Kernel::FILE_DOCKER_COMPOSE_SERVICES);

        if ( ! $this->dockerComposeService->isRunning($servicesDockerFile, Config::SERVICES)) {
            $this->setUpServices($io);
        }

        if ($this->dockerComposeService->isRunning($dockerFile, $name)) {
            $io->success("Docker container $name is already running");
            $isRunning = true;
        } else {
            $this->dockerComposeService->up($dockerFile, $name, [Config::ENV_PORT => $port]);
            $isRunning = $this->dockerComposeService->isRunning($dockerFile, $name);
        }

        if ( ! $isRunning) {
            return Command::FAILURE;
        }

        $uri1 = 'https://localhost:' . $port;
        $uri2 = 'https://' . $name . '.test:' . $port;

        $io->success("You can visit the website at:\n$uri1\nor, if the domain is pointing to localhost:\n" . $uri2);

        return Command::SUCCESS;
    }

    public function attach(string $dockerFile, string $name): int
    {
        $container = $this->dockerComposeService->getContainerName($dockerFile, $name);

        $process = new Process(['docker', 'exec', '-it', $container, '/bin/bash']);
        $process->setTty(Process::isTtySupported());
        $process->setTimeout(null);
        $process->run();

        return Command::SUCCESS;
    }

    public function setUpServices(SymfonyStyle $io): void
    {
        $servicesDockerFile = $this->kernel->getCmsDir(Kernel::FILE_DOCKER_COMPOSE_SERVICES);

        $password = $io->askHidden('Enter the desired password for the local DB: ');
        $this->dockerComposeService->up($servicesDockerFile, Config::SERVICES, [Config::ENV_PASS => $password]);
    }
}