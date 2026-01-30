<?php

namespace KikCMS\Domain\App\Development\Docker;

use Symfony\Component\Process\Process;

readonly class DockerService
{
    public function __construct(
        private DockerConfigService $dockerConfigService
    ) {}

    public function down(string $name, int $port): void
    {
        $dockerFile = $this->dockerConfigService->getDockerFile();

        $process = new Process(['docker', 'compose', '-f', $dockerFile, '-p', $name, 'down']);

        $process->setEnv(['SITE_ALIAS' => $name, 'SITE_PORT' => $port]);

        $process->setTty(Process::isTtySupported())->run(function ($type, $buffer) {
            echo $buffer;
        });
    }

    public function isRunning(string $name): bool
    {
        $dockerFile = $this->dockerConfigService->getDockerFile();

        $check = new Process(['docker', 'compose', '-f', $dockerFile, '-p', $name, 'ps', '--status', 'running', '-q']);
        $check->run();

        return (bool) $check->getOutput();
    }

    public function up(string $name, int $port): void
    {
        $dockerFile = $this->dockerConfigService->getDockerFile();

        $command = ['docker', 'compose', '-f', $dockerFile, '-p', $name, 'up', '-d'];
        $process = new Process($command)->setEnv(['SITE_ALIAS' => $name, 'SITE_PORT' => $port]);

        $process->setTty(Process::isTtySupported())->run(function ($type, $buffer) {
            echo $buffer;
        });
    }
}