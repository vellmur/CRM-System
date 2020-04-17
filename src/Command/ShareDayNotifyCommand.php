<?php

namespace App\Command;

use App\Entity\Customer\Customer;
use App\Manager\MasterManager;
use App\Manager\MemberEmailManager;
use App\Service\MailService;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;

/**
 *
 * Class ShareDayNotifyCommand
 * @package App\Command
 */
class ShareDayNotifyCommand extends Command
{
    protected static $defaultName = 'app:customers:share-day-notify';

    // Counters for sent emails
    private $counter = 0;

    private $manager;

    private $masterManager;

    private $mailer;

    public function __construct(
        MemberEmailManager $manager,
        MasterManager $masterManager,
        MailService $mailService
    ) {
        parent::__construct();

        $this->manager = $manager;
        $this->masterManager = $masterManager;
        $this->mailer = $mailService;
    }

    protected function configure()
    {
        $this->setDescription('Send notifications to customers about their delivery day!');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // outputs multiple lines to the console (adding "\n" at the end of each line)
        $output->writeln([
            'Start checking',
            '============',
            ''
        ]);

        // If today is not Saturday, set date to next Saturday (we getting right week number from last day of week)
        $weekDate = new \DateTime("midnight");
        if ($weekDate->format('w') != 6) $weekDate->modify('next Saturday');

        try {
            foreach ($this->manager->getSoftwareClients() as $client) {
                // If client is active and he didn't suspend current week -> send notifications
                if (!$this->manager->isWeekSuspended($client, $weekDate)) {
                    // Email creates once for each client, then we save email in vars and add recipients to email
                    $email = null;

                    $customers = $this->manager->getLeadsAndContacts($client);

                    foreach ($customers as $customer) {
                        if (!$email) $email = $this->manager->createAutoLog($client, 'delivery_day');
                        $delivered = $this->mailer->sendAutomatedEmail($email, $customer);

                        $this->counter++;

                        $this->outputResults($output, $customer, 'delivery_day', $delivered);
                    }
                }
            }
        } catch (Exception $e) {
            $output->write('Error: ' . $e->getMessage());
        }

        $output->writeln([
            'Successful checking',
            '============',
            'Emails was sent to customers: ' . $this->counter
        ]);

        return 0;
    }

    /**
     * @param OutputInterface $output
     * @param Customer $customer
     * @param $type
     * @param $delivered
     */
    public function outputResults(OutputInterface $output, Customer $customer, $type, $delivered)
    {
        $message = ($delivered ? 'received' : 'failed') . ' ' . $type . ' notification!';

        $output->writeln($this->counter . '. ' . $customer->getFullname() . ' ' .  $message);
    }
}