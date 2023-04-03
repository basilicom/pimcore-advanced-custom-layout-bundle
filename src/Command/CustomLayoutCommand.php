<?php

namespace Basilicom\AdvancedCustomLayoutBundle\Command;

use Basilicom\AdvancedCustomLayoutBundle\Service\CustomLayoutService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CustomLayoutCommand extends Command
{
    private const COMMAND = 'basilicom:custom-layouts:load';
    private CustomLayoutService $customLayoutService;

    public function __construct(CustomLayoutService $customLayoutService, string $name = null)
    {
        parent::__construct($name);
        $this->customLayoutService = $customLayoutService;
    }

    protected function configure()
    {
        $this->setName(self::COMMAND)
            ->setDescription('Load all custom layouts.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->customLayoutService->importAllCustomLayouts();

        return Command::SUCCESS;
    }
}
