<?php

namespace KikCMS\Command\App;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Contracts\Service\Attribute\Required;

abstract class AppCommand extends Command
{
    protected KernelInterface $kernel;

    #[Required]
    public function setKernel(KernelInterface $kernel): void
    {
        $this->kernel = $kernel;
    }

    abstract protected function executeScoped(SymfonyStyle $io): int;

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if ( ! $this->kernel->isProject()) {
            $io->error('This command can only be executed from the project scope');
            return Command::INVALID;
        }

        return $this->executeScoped($io);
    }
}