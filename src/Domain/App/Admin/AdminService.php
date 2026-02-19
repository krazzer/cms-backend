<?php

namespace KikCMS\Domain\App\Admin;

use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use ZipArchive;

readonly class AdminService
{
    public function __construct(
        #[Autowire('%admin.dist.latest%')]
        private string $latestDistUrl,
    ) {}

    public function update(string $adminDir, SymfonyStyle $io): void
    {
        $io->text('Updating latest admin release from ' . $this->latestDistUrl . '...');

        $zipFilePath = $adminDir . '.zip';

        file_put_contents($zipFilePath, file_get_contents($this->latestDistUrl));

        $zip = new ZipArchive();

        $zip->open($zipFilePath);
        $zip->extractTo($adminDir);
        $zip->close();

        unlink($zipFilePath);
    }
}