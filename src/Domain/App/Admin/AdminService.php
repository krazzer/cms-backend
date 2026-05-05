<?php

namespace KikCMS\Domain\App\Admin;

use DateTime;
use DateTimeZone;
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
        $io->comment('Updating latest admin release from ' . $this->latestDistUrl . '...');

        $headers = get_headers($this->latestDistUrl, 1);

        if (isset($headers['Last-Modified'])) {
            $date = new DateTime($headers['Last-Modified']);
            $date->setTimezone(new DateTimeZone(date_default_timezone_get()));

            $io->comment('Last modified: ' . $date->format('D, d M Y H:i:s T'));
        }

        $data = file_get_contents($this->latestDistUrl);

        $zipFilePath = $adminDir . '.zip';

        if ($data === false) {
            $io->error('Download failed');
            return;
        }

        $result = file_put_contents($zipFilePath, $data);

        if ($result === false) {
            $io->error('Failed to store zip file locally');
            return;
        }

        // Open zip
        $zip = new ZipArchive();
        $res = $zip->open($zipFilePath);

        if ($res !== true || ! $zip->extractTo($adminDir)) {
            $io->error('Failed to extract zip file');
            return;
        }

        $zip->close();

        unlink($zipFilePath);

        $io->success('Admin updated');
    }
}