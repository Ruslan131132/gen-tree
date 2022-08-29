<?php

namespace App\Command;

use App\Service\GenTreeGenerator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateGenTreeCommand extends Command
{
    /**
     * @property GenTreeGenerator $generator
     */
    private $generator;

    public function __construct(GenTreeGenerator $generator)
    {
        $this->generator = $generator;

        parent::__construct();
    }

    // название команды
    protected static $defaultName = 'app:generate-tree';

    protected function configure(): void
    {
        $this
            // данные на вход программе
            ->addArgument('input', InputArgument::REQUIRED, 'Входящий файл')
            ->addArgument('output', InputArgument::REQUIRED, 'Результат');
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->generator->generateJSON($input->getArgument('input'), $input->getArgument('output'));


        $output->writeln([
            '',
            '====================',
            'Генерация завершена!',
        ]);

        return Command::SUCCESS;
    }
}