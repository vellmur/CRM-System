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
 * Send 5 types of shares notifications to customer (Activation, Weekly, Renewal, Lapsed, Feedback)
 *
 * Activation notification: If share is PENDING and less than 7 days lefts to the start date or customer is not active.
 * Weekly notification: If 2 days lefts to next share pickup date.
 * Renewal notification: Sends 1 week (7 days) before the renewal date and last share pickup date.
 * Lapsed notification: If renewal date is the past and share status is not equal to lapsed.
 *
 * Feedback notification: If share day of customer was 2 days ago, send feedback notification.
 *
 * This cron runs each hour and sends to members on time that defined in $workTime variable.
 * Time depends on client timezone. If client did'nt save timezone in profile, default timezone - 'Europe/Paris'.
 *
 * Class MembershipNotifyCommand
 * @package App\Command
 */
class SharesNotifyCommand extends Command
{
    protected static $defaultName = 'app:shares:notify';

    private $counters = [
        'weekly' => 0,
        'renewal' => 0,
        'lapsed' => 0,
        'activation' => 0,
        'feedback' => 0,
        'total' => 0
    ];

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

    // Here saves time of script works for client or default timezones
    private $workTime = 22;

    private $defaultTime = 4;

    protected function configure()
    {
        $this->setDescription('Send notifications to members about their shares!');
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
                // If client timezone (when exists) time match with $workTime or now $defaultTime hours in Europe/Paris
               // if ($this->timeMatch($client->getTimezone())) {

                // If client didn't suspend current week -> send notifications
                if (!$this->manager->isWeekSuspended($client, $weekDate)) {
                    // Emails creates once for each client, then we save email in vars and add recipients to email
                    $weeklyEmail = null;
                    $renewalEmail = null;
                    $lapsedEmail = null;
                    $feedbackEmail = null;

                    // Renewal/Lapsed emails sends max once a day, this arrays helps to prevent multiple sends
                    $renewal = [];
                    $lapsed = [];
                    $feedback = [];

                    // Send weekly/renewal/lapsed notifications
                    foreach ($this->manager->getNotLapsedShares($client) as $share) {
                        $member = $share->getMember();

                        $notify = $this->manager->getShareStatusNotify($share);

                        if ($notify) {
                            if (isset($notify['weekly'])) {
                                if (!$weeklyEmail) $weeklyEmail = $this->manager->createAutoLog($client, 'weekly');
                                $delivered = $this->mailer->sendAutomatedEmail($weeklyEmail, $member, $notify['weekly']);

                                $this->counters['weekly']++;

                                $this->outputResults($output, $member, 'weekly', $share->getShareName(), $delivered);
                            }

                            if (isset($notify['renewal']) && !in_array($member->getId(), $renewal)) {
                                if (!$renewalEmail) $renewalEmail = $this->manager->createAutoLog($client, 'renewal');
                                $delivered = $this->mailer->sendAutomatedEmail($renewalEmail, $member, $notify['renewal']);

                                $this->counters['renewal']++;
                                $renewal[] = $member->getId();

                                $this->outputResults($output, $member, 'renewal', $share->getShareName(), $delivered);
                            }

                            if (isset($notify['lapsed']) && !in_array($member->getId(), $lapsed)) {
                                if (!$lapsedEmail) $lapsedEmail = $this->manager->createAutoLog($client, 'lapsed');
                                $delivered = $this->mailer->sendAutomatedEmail($lapsedEmail, $member, $notify['lapsed']);

                                $this->counters['lapsed']++;
                                $lapsed[] = $member->getId();

                                $this->outputResults($output, $member,'lapsed', $share->getShareName(), $delivered);
                            }
                        }

                        // Send feedback if customer share day was 2 days ago (max once a day)
                        if ($this->manager->mustReceiveFeedback($share) && !in_array($member->getId(), $feedback)) {
                            if (!$feedbackEmail) $feedbackEmail = $this->manager->createAutoLog($client, 'feedback');
                            $delivered = $this->mailer->sendAutomatedEmail($feedbackEmail, $member, $share);

                            $this->counters['feedback']++;
                            $feedback[] = $member->getId();

                            $this->outputResults($output, $member, 'feedback', $share->getShareName(), $delivered);
                        }
                    }

                    // Get pending shares or shares with not active members
                    foreach ($this->manager->getNotActiveShares($client) as $share) {
                        $member = $share->getMember();

                        // If share is pending or customer is not active and to the start date less that 7 days
                        if (($share->getStatusName() != 'ACTIVE' || !$member->isActivated()) && $this->countDaysLeft($share->getStartDate()) <= 7) {
                            if ($share->getStatusName() != 'ACTIVE') $share->setStatusByName('ACTIVE');

                            $delivered = null;

                            // Activate customer, If he is not activated yet (this happens just once)
                            if (!$member->isActivated()) {
                                if ($this->manager->notifyEnabled($member, 'activation')) {
                                    $email = $this->manager->createAutoLog($client, 'activation');
                                    $delivered = $this->mailer->sendAutomatedEmail($email, $member, $share);
                                }

                                $member->setIsActivated(true);
                            }

                            $this->manager->flush();

                            $this->counters['activation']++;

                            $this->outputResults($output, $member, 'activated', $share->getShareName(), $delivered);
                        }
                    }
                }
            }
        } catch (Exception $e) {
            $output->write('Error: ' . $e->getMessage());
        }

        $output->writeln([
            'Successful checking',
            '============',
            'Weekly emails was sent to members: ' . $this->counters['weekly'],
            'Renewal emails was sent to members: ' . $this->counters['renewal'],
            'Lapsed emails was sent to members: ' . $this->counters['lapsed'],
            'Feedback emails was sent to members: ' . $this->counters['feedback'],
            'Activated shares or members: ' . $this->counters['activation'],
            'Total: ' . $this->counters['total']
        ]);

        return 0;
    }

    /**
     * @param OutputInterface $output
     * @param Customer $member
     * @param $type
     * @param $shareName
     * @param $delivered
     */
    public function outputResults(OutputInterface $output, Customer $member, $type, $shareName, $delivered = null)
    {
        if ($delivered !== null) {
            $message = ($delivered ? 'received' : 'failed') . ' ' . $type . ' for ' . $shareName .' notification!';
        } else {
            $message = $type . ' for ' . $shareName . '!';
        }

        $this->counters['total']++;

        $output->writeln($this->counters['total'] . '. ' . $member->getFullname() . ' ' .  $message);
    }

    /**
     * @param $date
     * @return mixed
     */
    public function countDaysLeft($date)
    {
        $startDate = strtotime(date_format($date, 'Y-m-d H:i:s'));
        $now = strtotime(date_format(new \DateTime(), 'Y-m-d H:i:s'));

        $diffInDays = floor(($startDate - $now) / (60 * 60 * 24));

        return $diffInDays + 1;
    }

    /**
     * Return time (hour) in client timezone or in default timezone - 'Europe/Paris'.
     *
     * @param $timezone
     * @return string
     */
    public function getClientTime($timezone)
    {
        // Get current date and hour for client timezone, or by default timezone - 'Europe/Paris'
        if (!$timezone) $timezone = 'Europe/Paris';
        $now = new \DateTime('', new \DateTimeZone($timezone));

        return $now->format('H');
    }

    /**
     * Return true if client local timezone == $workTime, or default timezone == $defaultTime.
     *
     * @param $clientTimezone
     * @return bool
     */
    public function timeMatch($clientTimezone)
    {
        $time = $this->getClientTime($clientTimezone);

        return ($clientTimezone && $time == $this->workTime) || (!$clientTimezone && $time == $this->defaultTime);
    }
}