<?php

namespace App\Command;

use App\Entity\Customer\Customer;
use App\Manager\MasterManager;
use App\Manager\MemberEmailManager;
use App\Service\Mail\Sender;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;

/**
 *
 * Class WeeklyFeedbackReportCommand
 * @package App\Command
 */
class WeeklyFeedbackReportCommand extends Command
{
    protected static $defaultName = 'app:customers:weekly-feedback-report';

    private $counter = 0;

    private $manager;

    private $masterManager;

    private $mailer;

    public function __construct(
        MemberEmailManager $manager,
        MasterManager $masterManager,
        Sender $sender
    ) {
        parent::__construct();

        $this->manager = $manager;
        $this->masterManager = $masterManager;
        $this->mailer = $sender;
    }

    protected function configure()
    {
        $this->setDescription('Send notifications to clients about feedback from members!');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // outputs multiple lines to the console (adding "\n" at the end of each line)
        $output->writeln([
            'Start checking',
            '============',
            ''
        ]);

        try {
            foreach ($this->manager->getSoftwareClients() as $client) {
                // If week is not suspended by client
                if (!$this->manager->isWeekSuspended($client)) {
                    $feedbackReport = $this->manager->getFeedbackReport($client);

                    if ($feedbackReport['totalMembers'] > 0) {
                        // Send customer review to client
                        $this->mailer->sendMail(
                            'Black Dirt Software',
                            $client->getContactEmail(),
                            'emails/customer/weekly_feedback_report.html.twig',
                            'Members feedback report',
                            $feedbackReport
                        );

                        $this->counter++;
                    }
                }
            }
        } catch (Exception $e) {
            $output->write('Error: ' . $e->getMessage());
        }

        $output->writeln([
            'Successful checking',
            '============',
            'Emails was sent to clients: ' . $this->counter
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