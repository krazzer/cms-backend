<?php

namespace KikCMS\Domain\App\Development\Docker;

use Symfony\Component\Process\Process;

readonly class DockerComposeService
{
    public function up(string $dockerFile, string $project, int $port): void
    {
        $this->runCompose($dockerFile, $project, ['up', '-d'], $port);
    }

    public function down(string $dockerFile, string $project, int $port): void
    {
        $this->runCompose($dockerFile, $project, ['down'], $port);
    }

    public function isRunning(string $dockerFile, string $project): bool
    {
        $process = $this->runCompose($dockerFile, $project, ['ps', '--status', 'running', '-q'], null, false);

        return (bool) $process->getOutput();
    }

    public function getContainerName(string $dockerFile, string $name): string
    {
        $process = $this->runCompose($dockerFile, $name, ['ps', '--status', 'running', '--format', '{{.Name}}'], null, false);

        return trim($process->getOutput());
    }

    private function runCompose(string $file, string $name, array $arg, ?int $port, bool $output = true): Process
    {
        $command = array_merge(['docker', 'compose', '-f', $file, '-p', $name], $arg);

        $process = new Process($command);
        $process->setEnv(['PORT' => $port, 'ALIAS' => $name]);
        $process->setTty(Process::isTtySupported() && $output);

        $process->run(function ($type, $buffer) use ($output) {
            if ($output) {
                print $buffer;
            }
        });

        return $process;
    }
}
