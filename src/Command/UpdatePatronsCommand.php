<?php

namespace App\Command;

use App\Manager\MembershipManager;
use App\Service\Mail\Sender;
use App\Service\TimezoneService;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Patron must become a Lead one day after their purchase
 *
 * Class CheckLapsedCommand
 * @package App\Command
 */
class UpdatePatronsCommand extends Command
{
    protected static $defaultName = 'app:customers:patrons-update';

    private $manager;

    private $timezoneService;

    private $mailer;

    private $runTime = 0;

    private $serverRunTime = 3;

    public function __construct(MembershipManager $manager, TimezoneService $timezoneService, Sender $sender)
    {
        parent::__construct();

        $this->manager = $manager;
        $this->timezoneService = $timezoneService;
        $this->mailer = $sender;
    }

    protected function configure()
    {
        $this->setDescription('Update Patrons and make them Lead');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // outputs multiple lines to the console (adding "\n" at the end of each line)
        $output->writeln([
            'Start checking',
            '============'
        ]);

        $updatedPatrons = 0;

        try {
            $clients = $this->manager->getClientsWithPatrons();

            foreach ($clients as $client) {
                if ($this->timezoneService->timeMatch($client->getTimezone(), $this->runTime, $this->serverRunTime)) {
                    foreach ($client->getCustomers() as $customer) {
                        $customer->setIsLead(true);

                        $output->writeln($customer->getFullname() . ' status was updated from Patron to Lead;');
                        $updatedPatrons++;
                    }

                    $this->manager->flush();
                }
            }
        } catch (Exception $e) {
            $output->write('Error: ' . $e->getMessage());
        }

        $output->writeln([
            '============',
            'Successful checking',
            '============',
            'Number of Patrons that becomed a Lead: ' . $updatedPatrons . '.',
        ]);

        return 0;
    }
}