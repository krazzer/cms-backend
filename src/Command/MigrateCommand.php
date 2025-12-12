<?php

namespace KikCMS\Command;

use KikCMS\Domain\Migrate\MigrateService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:migrate',
    description: 'Execute specific migrations (pages, pageLanguage)',
)]
class MigrateCommand extends Command
{
    public function __construct(readonly MigrateService $migrateService)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('type', InputArgument::REQUIRED,
            'Which migration do you want to execute? (pages, pageLanguage)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io   = new SymfonyStyle($input, $output);
        $type = $input->getArgument('type');

        match ($type) {
            'pages' => $this->migrateService->migratePages(),
            'pageLanguage' => $this->migrateService->migratePageLanguage(),
            default => $io->error("Unknown migration type: $type"),
        };

        $io->success("Migration '$type' completed.");
        return Command::SUCCESS;
    }
}