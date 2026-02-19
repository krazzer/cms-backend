<?php

namespace KikCMS\Domain\App\Development\Docker;

use Symfony\Component\Process\Process;

readonly class DockerComposeService
{
    public function up(string $dockerFile, string $name, array $env): void
    {
        $this->runCompose($dockerFile, $name, ['up', '-d'], $env);
    }

    public function down(string $dockerFile, string $name, int $port): void
    {
        $this->runCompose($dockerFile, $name, ['down'], [Config::ENV_PORT => $port, Config::ENV_ALIAS => $name]);
    }

    public function isRunning(string $dockerFile, string $name): bool
    {
        $process = $this->runCompose($dockerFile, $name, ['ps', '--status', 'running', '-q'], [], false);

        return (bool) $process->getOutput();
    }

    public function getContainerName(string $dockerFile, string $name): string
    {
        $process = $this->runCompose($dockerFile, $name, ['ps', '--status', 'running', '--format', '{{.Name}}'], [], false);

        return trim($process->getOutput());
    }

    private function runCompose(string $file, string $name, array $arg, array $env = [], bool $output = true): Process
    {
        $command = array_merge(['docker', 'compose', '-f', $file, '-p', $name], $arg);

        $process = new Process($command);
        $process->setEnv($env);
        $process->setTty(Process::isTtySupported() && $output);
        $process->setTimeout(null);

        $process->run(function ($type, $buffer) use ($output) {
            if ($output) {
                print $buffer;
            }
        });

        return $process;
    }
}
