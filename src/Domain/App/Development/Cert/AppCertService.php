<?php

namespace KikCMS\Domain\App\Development\Cert;

use KikCMS\Kernel;

readonly class AppCertService extends AbstractCertService
{
    protected function getPaths(): array
    {
        $certsDir    = $this->kernel->getAppDir(Kernel::DIR_CERTS);
        $certFile    = $this->kernel->getAppDir(Kernel::FILE_CERT);
        $certKeyFile = $this->kernel->getAppDir(Kernel::FILE_CERT_KEY);

        return [$certsDir, $certFile, $certKeyFile];
    }
}