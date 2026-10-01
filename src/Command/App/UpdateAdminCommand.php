<?php

namespace KikCMS\Command\App;

use KikCMS\Domain\App\Admin\AdminService;
use KikCMS\Domain\App\Path\PathConfig;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'kikcms:app:update-admin',
    description: 'Update the admin panel for the CMS of this app',
)]
class UpdateAdminCommand extends AppCommand
{
    public function __construct(readonly AdminService $adminService)
    {
        parent::__construct();
    }

    protected function executeScoped(SymfonyStyle $io): int
    {
        $adminDir = $this->kernel->getAppDir(PathConfig::DIR_ADMIN);

        $this->adminService->update($adminDir, $io);

        return Command::SUCCESS;
    }
}