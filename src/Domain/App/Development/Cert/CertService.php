<?php

namespace KikCMS\Domain\App\Development\Cert;

use KikCMS\Kernel;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Process\Process;

readonly class CertService
{
    public function __construct(
        private KernelInterface $kernel
    ) {}

    public function certsAreInPlace(string $name): bool
    {
        $certsDir = $this->kernel->getAppDir(Kernel::DIR_CERTS);

        $certFile    = $this->kernel->getAppDir(Kernel::FILE_CERT);
        $certKeyFile = $this->kernel->getAppDir(Kernel::FILE_CERT_KEY);

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
}