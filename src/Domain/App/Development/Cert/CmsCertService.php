<?php

namespace KikCMS\Domain\App\Development\Cert;

use KikCMS\Kernel;

readonly class CmsCertService extends AbstractCertService
{
    protected function getPaths(): array
    {
        $certsDir    = $this->kernel->getCmsDir(Kernel::DIR_CERTS);
        $certFile    = $this->kernel->getCmsDir(Kernel::FILE_CERT);
        $certKeyFile = $this->kernel->getCmsDir(Kernel::FILE_CERT_KEY);

        if (is_dir($certsDir) === false) {
            mkdir($certsDir);
        }

        return [$certsDir, $certFile, $certKeyFile];
    }
}