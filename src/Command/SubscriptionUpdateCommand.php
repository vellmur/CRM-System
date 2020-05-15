<?php

namespace App\Command;

use App\Manager\CommandManager;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SubscriptionUpdateCommand extends Command
{
    protected static $defaultName = 'app:subscription:update';

    private $manager;

    public function __construct(CommandManager $manager)
    {
        parent::__construct();

        $this->manager = $manager;
    }

    protected function configure()
    {
        $this->setDescription('Update statuses of buildings subscriptions.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // outputs multiple lines to the console (adding "\n" at the end of each line)
        $output->writeln('Start checking!');
        $output->writeln('');

        $updatedNum = 0;

        $output->writeln([
            'Successful checking',
            '============',
            'Updated subscriptions number ' . $updatedNum
        ]);

        return 0;
    }
}