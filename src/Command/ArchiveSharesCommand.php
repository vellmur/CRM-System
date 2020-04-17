<?php

namespace App\Command;

use App\Entity\Customer\Customer;
use App\Entity\Customer\CustomerShare;
use App\Manager\MemberEmailManager;
use App\Manager\MembershipManager;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * If after lapsed status passed 6 days, archive a share (remove pickups and destroy relation with customer).
 * Patrons shares becomes lapsed on next day after order date (start date).
 *
 * Class CheckLapsedCommand
 * @package App\Command
 */
class ArchiveSharesCommand extends Command
{
    protected static $defaultName = 'app:shares:archive';

    private $manager;

    private $emailManager;

    private $archivedShares = 0;

    private $sentEmails = 0;

    public function __construct(MembershipManager $manager, MemberEmailManager $emailManager)
    {
        parent::__construct(self::$defaultName);

        $this->manager = $manager;
        $this->emailManager = $emailManager;
    }

    protected function configure()
    {
        $this->setDescription('Archive lapsed shares');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // outputs multiple lines to the console (adding "\n" at the end of each line)
        $output->writeln([
            'Start checking',
            '============'
        ]);

        try {
            $clients = $this->emailManager->getSoftwareClients();

            foreach ($clients as $client) {
                $shares = $this->manager->getSharesToArchive($client);

                if (count($shares) > 0) {
                    /** @var CustomerShare $share */
                    foreach ($shares as $share) {
                        $daysToLapsed = $this->countDaysLeft($share->getRenewalDate());

                        // Archive share if it is 7 days gone from last share date or share type == Patron
                        if ($daysToLapsed < - 6 || ($daysToLapsed < 0 && $share->getTypeName() == 'PATRON')) {
                            $this->manager->getMemberManager()->deleteShare($share);

                            $this->outputResults($output, $share->getMember(), 'archived share' , $share->getShareName(), true);
                            $this->archivedShares++;
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
            'Shares archived: ' . $this->archivedShares,
            'Emails was sent: ' . $this->sentEmails,
        ]);

        return 0;
    }

    /**
     * @param $date
     * @return mixed
     */
    public function countDaysLeft($date)
    {
        $startDate = strtotime(date_format($date, 'Y-m-d H:i:s'));
        $now = strtotime(date_format(new \DateTime("midnight"), 'Y-m-d H:i:s'));

        $diffInDays = floor(($startDate - $now) / (60 * 60 * 24));

        return $diffInDays;
    }

    /**
     * @param OutputInterface $output
     * @param Customer $member
     * @param $type
     * @param $share
     * @param $successful
     */
    public function outputResults(OutputInterface $output, Customer $member, $type, $share, $successful)
    {
        $message = $successful ? 'successfully ' : 'failed ';
        $message .=  $type . ' for the ' . $share;

        $output->writeln($this->sentEmails . '. ' . $member->getFullname() . ' ' . $message . '!');
    }
}