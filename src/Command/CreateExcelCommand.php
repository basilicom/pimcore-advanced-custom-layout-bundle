<?php

namespace Basilicom\AdvancedCustomLayoutBundle\Command;

use Basilicom\AdvancedCustomLayoutBundle\Service\ExcelCreator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateExcelCommand extends Command
{
    private const COMMAND = 'basilicom:custom-layouts:create-excel';
    private ExcelCreator $fileCreator;

    public function __construct(ExcelCreator $fileCreator, string $name = null)
    {
        parent::__construct($name);
        $this->fileCreator = $fileCreator;
    }

    protected function configure()
    {
        $this->setName(self::COMMAND)
            ->setDescription(
                'Create an Excel file with one datasheet per class and all fields including their configuration.'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->fileCreator->create();

        return Command::SUCCESS;
    }
}
