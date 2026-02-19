<?php

namespace KikCMS\Domain\App\Development\Cert;

use KikCMS\Kernel;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Process\Process;

abstract readonly class AbstractCertService
{
    public function __construct(
        protected KernelInterface $kernel
    ) {}

    public function certsAreInPlace(string $name): bool
    {
        list($certsDir, $certFile, $certKeyFile) = $this->getPaths();

        $certSnakeFile    = $this->kernel->getCmsDir(Kernel::FILE_SNAKE_CERT);
        $certSnakeKeyFile = $this->kernel->getCmsDir(Kernel::FILE_SNAKE_CERT_KEY);

        if (file_exists($certFile) && file_exists($certKeyFile)) {
            return file_get_contents($certFile) !== file_get_contents($certSnakeFile);
        }

        $command = ['mkcert', '-cert-file', 'cert.crt', '-key-file', 'cert.key', 'localhost', "$name.test"];

        new Process($command, $certsDir)->setTty(Process::isTtySupported())->run(function ($type, $buffer) {
            echo $buffer;
        });

        new Process(['mkcert', '-install'])->run(function ($type, $buffer) {
            echo $buffer;
        });

        if (file_exists($certFile) && file_exists($certKeyFile)) {
            return true;
        }

        copy($certSnakeFile, $certFile);
        copy($certSnakeKeyFile, $certKeyFile);

        return false;
    }

    public function showCertWarning(SymfonyStyle $io, string $name): void
    {
        list($location) = $this->getPaths();

        $command = "mkcert -cert-file cert.crt -key-file cert.key localhost $name.test";

        $io->warning("Certificate files are missing, now using fake files. Generate them using:\n$command, " .
            "overwrite the files in: $location, and then restart the container.");
    }

    abstract protected function getPaths(): array;
}