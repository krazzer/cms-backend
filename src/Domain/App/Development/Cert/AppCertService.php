<?php

namespace KikCMS\Domain\App\Development\Cert;

use KikCMS\Domain\App\Path\PathConfig;

readonly class AppCertService extends AbstractCertService
{
    protected function getPaths(): array
    {
        $certsDir    = $this->kernel->getAppDir(PathConfig::DIR_CERTS);
        $certFile    = $this->kernel->getAppDir(PathConfig::FILE_CERT);
        $certKeyFile = $this->kernel->getAppDir(PathConfig::FILE_CERT_KEY);

        return [$certsDir, $certFile, $certKeyFile];
    }
}