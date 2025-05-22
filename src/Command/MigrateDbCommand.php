<?php

namespace App\Command;

use App\Entity\Migrate\MigrateService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:migrate-db',
    description: 'Migrate the old DB structure to the new',
)]
class MigrateDbCommand extends Command
{
    /** @var MigrateService */
    private MigrateService $migrateService;

    /**
     * @return void
     */
    public function __construct(MigrateService $migrateService)
    {
        parent::__construct();

        $this->migrateService = $migrateService;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $this->migrateService->migrate();

        $io->note('Ok');

        return Command::SUCCESS;
    }
}
