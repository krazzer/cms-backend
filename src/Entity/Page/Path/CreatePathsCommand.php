<?php

namespace KikCMS\Entity\Page\Path;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'kikcms:page:create-paths',
    description: 'Look for pages without paths and create them. This can be useful when DB changes are made outside of 
        the application',
)]
class CreatePathsCommand extends Command
{
    public function __construct(private readonly PathService $pathService)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $pagesWithoutPath = $this->pathService->getPagesWithoutPath();

        $pagesUpdated = 0;

        foreach ($pagesWithoutPath as $page) {
            if ($this->pathService->updatePath($page)) {
                $this->entityManager->persist($page);
                $pagesUpdated++;
            }
        }

        $this->entityManager->flush();

        $io->success(sprintf('Updated %d page%s', $pagesUpdated, $pagesUpdated === 1 ? '' : 's'));

        return Command::SUCCESS;
    }
}