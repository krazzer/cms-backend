<?php

namespace KikCMS\Domain\App\Development\Cert;

use KikCMS\Domain\App\Path\PathConfig;

readonly class CmsCertService extends AbstractCertService
{
    protected function getPaths(): array
    {
        $certsDir    = $this->kernel->getCmsDir(PathConfig::DIR_CERTS);
        $certFile    = $this->kernel->getCmsDir(PathConfig::FILE_CERT);
        $certKeyFile = $this->kernel->getCmsDir(PathConfig::FILE_CERT_KEY);

        if (is_dir($certsDir) === false) {
            mkdir($certsDir);
        }

        return [$certsDir, $certFile, $certKeyFile];
    }
}