<?php

namespace KikCMS\Command\App;

use KikCMS\Domain\App\Admin\AdminService;
use KikCMS\Kernel;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpKernel\KernelInterface;

#[AsCommand(
    name: 'kikcms:app:update-admin',
    description: 'Update the admin panel for the CMS of this app',
)]
class UpdateAdminCommand extends Command
{
    public function __construct(readonly AdminService $adminService, private readonly KernelInterface $kernel)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $adminDir = $this->kernel->getAppDir(Kernel::DIR_ADMIN);

        $this->adminService->update($adminDir, $io);

        return Command::SUCCESS;
    }
}