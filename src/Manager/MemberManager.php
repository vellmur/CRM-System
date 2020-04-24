<?php

namespace App\Manager;

use App\Data\CountryInfo;
use App\Entity\Customer\CustomerEmailNotify;
use App\Entity\Customer\Email\AutoEmail;
use App\Entity\Customer\Invoice;
use App\Entity\Customer\CustomerShare;
use App\Entity\Customer\Pickup;
use App\Entity\Customer\Product;
use App\Entity\Customer\Share;
use App\Entity\Customer\SuspendedWeek;
use App\Entity\Customer\Workday;
use App\Repository\MemberRepository;
use App\Entity\Client\Client;
use App\Entity\Customer\Location;
use App\Entity\Customer\Customer;
use Doctrine\ORM\EntityManagerInterface;
use DrewM\MailChimp\MailChimp;
use App\Entity\Client\PaymentSettings;

class MemberManager
{
    private $em;

    private $repository;

    private $emailManager;

    public function __construct(EntityManagerInterface $em, MemberRepository $repository, MemberEmailManager $emailManager)
    {
        $this->em = $em;
        $this->repository = $repository;
        $this->emailManager = $emailManager;
    }

    /**
     * @return MemberEmailManager
     */
    public function getEmailManager()
    {
        return $this->emailManager;
    }

    /**
     * @param $token
     * @return Client|null|object
     */
    public function findClientByToken($token)
    {
        return $this->em->getRepository(Client::class)->findOneBy(['token' => $token]);
    }

    /**
     * @param Customer $customer
     * @return Customer
     * @throws \Exception
     */
    public function addMember(Customer $customer)
    {
        $now = new \DateTime();
        $customer->setToken($customer->getFullname() . $now->format('Y-m-d H:i:s'));

        // Add all email notifies to a customer, add pickups and update share statuses
        $this->activateNotifications($customer);
        $this->updatePickups($customer);

        if ($customer->getIsLead() && count($customer->getShares())) $customer->setIsLead(false);

        $this->em->persist($customer);
        $this->em->flush();

        return $customer;
    }

    /**
     * @param Customer $contact
     * @param Client $client
     * @throws \Exception
     */
    public function addOrUpdateContact(Customer &$contact, Client $client)
    {
        $member = $this->findOneByEmailOrPhone($client, $contact->getEmail());

        // Set contact to existed customer (if found) or add new customer as contact
        if ($member) {
            // Update customer name if not given
            if (strlen($contact->getFullname()) > 0) {
                if (strlen($contact->getFirstname()) > 0) $member->setFirstname($contact->getFirstname());
                if (strlen($contact->getLastname()) > 0) $member->setLastname($contact->getLastname());

                $this->em->flush();
            }

            $contact = $member;
        } elseif (strlen($contact->getFirstname()) > 0 && strlen($contact->getLastname()) > 0) {
            $contact = $this->addMember($contact);
        }
    }

    /**
     * @param Customer $member
     */
    public function activateNotifications(Customer $member)
    {
        // Add all email notifies to a customer
        foreach (AutoEmail::EMAIL_TYPES as $key => $type) {
            $notify = new CustomerEmailNotify();
            $notify->setNotifyType($key);
            $this->em->persist($notify);

            $member->addNotification($notify);
        }
    }

    /**
     * Activate customer if one of shares have ACTIVE status
     * Activated status change just once, after one of first shares in a system starts to be Active
     *
     * Return false or one of activated share (we will use this share for send it to the customer activation email)
     *
     * @param Customer $customer
     * @return CustomerShare|bool|mixed
     * @throws \Exception
     */
    public function checkActivation(Customer $customer)
    {
        $activeShare = false;

        // If week is not suspended (we do not send activation email on suspended weeks)
        if (!$this->isWeekSuspended($customer->getClient())) {
            foreach ($customer->getShares() as $share) {
                // If one of shares is ACTIVE, set customer isActivated to true and break
                if ($share->getStatusName() == 'ACTIVE') {
                    $customer->setIsActivated(true);
                    $this->em->flush();

                    $activeShare = $share;

                    break;
                }
            }
        }

        return $activeShare;
    }

    /**
     * In this function we update all shares and pickups.
     * Track all shares changes, correct pickup dates and number, suspended weeks.
     * Checking for both - MEMBERS and PATRONS
     *
     * @param Customer $member
     */
    public function updatePickups(Customer $member)
    {
        // Get all suspended weeks by client
        $suspendedWeeks = $this->getSuspendedWeeks($member->getClient());

        // Run through each share of client
        foreach ($member->getShares() as $share) {
            // Add new or update existed share pickups
            if (!count($share->getPickups())) {
                // Add pickups to new customer share
                $this->addSharePickups($share);
            } else {
                // Check changes to existed share for MEMBER or PATRON
                if ($share->getTypeName() == 'MEMBER') {
                    $this->checkCustomerShareUpdates($share);
                } else {
                    $this->checkPatronShareUpdates($share);
                }
            }

            if ($suspendedWeeks) {
                // Update suspended weeks for share pickups
                $this->checkSuspendedWeeks($share, $suspendedWeeks);
            }
        }
    }

    /**
     * @param CustomerShare $share
     */
    public function addSharePickups(CustomerShare $share)
    {
        $shareDates = $this->getShareDates($share);

        foreach ($shareDates as $date) {
            $this->addPickup($share, $date);
        }

        // If customer purchased more than one share, set type to MEMBER, else save as PATRON
        $share->setType($share->getPickupsNum() > 1 ? 1 : 2);

        // Automatically updates of share renewal date
        $this->updateShareRenewalDate($share);
    }

    /**
     * Track all changes (pickups num, pickup day) to customer shares
     *
     * @param CustomerShare $share
     */
    public function checkCustomerShareUpdates(CustomerShare $share)
    {
        $shareBefore = $this->em->getRepository(CustomerShare::class)->getOldShare($share);

        if ($share->getPickupsNum() != $shareBefore['pickupsNum'] || $share->getPickUpDay() != $shareBefore['pickupDay']) {
            // If pickups num was changed, find diff in pickups qty and add/remove share pickups
            if ($share->getPickupsNum() != $shareBefore['pickupsNum']) {
                $diff = $shareBefore['pickupsNum'] - $share->getPickupsNum();

                if ($diff < 0) {
                    // Add $num pickups to the end of share pickups, from last pickup date
                    for ($i = 0; $i < abs($diff); $i++) {
                        $lastPickup = $share->getPickups()[count($share->getPickups()) - 1];

                        $pickupDate = $this->getNextPickupDate($share,'+', 7, $lastPickup->getDate());
                        $this->addPickup($share, $pickupDate);
                    }
                } else {
                    $this->cutPickups($share);
                }
            }

            // If pickup day was changed, update all pickups dates for the share
            if ($share->getPickUpDay() != $shareBefore['pickupDay']) {
                $shareDates = $this->getShareDates($share);

                foreach ($shareDates as $key => $pickupDate) {
                    $share->getPickups()[$key]->setDate($pickupDate);
                }
            }

            // Automatically updates of share renewal date
            $this->updateShareRenewalDate($share);
        }
    }

    /**
     * Track all changes (start date, pickup day) to patron shares
     *
     * @param CustomerShare $share
     */
    public function checkPatronShareUpdates(CustomerShare $share)
    {
        $shareBefore = $this->em->getRepository(CustomerShare::class)->getOldShare($share);

        // If share with type PATRON was changed, update share pickups and share values
        if ($share->getStartDate() != $shareBefore['startDate'] || $share->getPickUpDay() != $shareBefore['pickupDay']) {
            // Patrons shares always have only one, same date, for pickup date, start date and renewal date
            $orderDate = $this->getShareDates($share)[0];
            $share->getPickups()[0]->setDate($orderDate);
            $share->setStartDate($orderDate);
            $share->setRenewalDate($orderDate);

            // Automatically updates of share renewal date
            $this->updateShareRenewalDate($share);
        }
    }

    /**
     * Suspend weeks defined by client and add needed number of not suspended pickups for the share
     *
     * @param CustomerShare $share
     * @param array $suspendedWeeks
     * @return array
     */
    public function checkSuspendedWeeks(CustomerShare $share, $suspendedWeeks)
    {
        // Save all added pickups by function
        $suspendedShares = [];

        foreach ($share->getPickups() as $key => $pickup) {
            // If week suspended by client and pickup not suspended yet -> suspend it
            if (!$pickup->isSuspended() && $this->isDateSuspended($suspendedWeeks, $pickup->getDate())) {
                $addedPickup = $this->suspendPickup($pickup);

                if ($addedPickup) {
                    $suspendedShares[] = $addedPickup;

                    // Suspend all forward weeks, if client suspended pickup week
                    while ($addedPickup && $this->isDateSuspended($suspendedWeeks, $addedPickup->getDate())) {
                        $addedPickup->setIsSuspended(true);
                        $addedPickup = $this->suspendPickup($pickup);

                        $suspendedShares[] = $addedPickup;
                    }
                }
            }
        }

        return $suspendedShares;
    }

    /**
     * @param $suspendedWeeks
     * @param $date \DateTime
     * @return bool
     */
    public function isDateSuspended($suspendedWeeks, $date)
    {
        if (isset($suspendedWeeks[$date->format('Y')]) && in_array($date->format('W'), $suspendedWeeks[$date->format('Y')])) {
            return true;
        }

        return false;
    }

    /**
     * Add pickup to a share
     *
     * @param CustomerShare $share
     * @param $date \DateTime
     * @return Pickup
     */
    public function addPickup(CustomerShare $share, $date)
    {
        $pickup = new Pickup();
        $pickup->setDate($date);

        $share->addPickup($pickup);

        return $pickup;
    }


    /**
     * Cut share pickups to needed pickups number (not skipped, not suspended)
     * pickups number - it is a total number of active share pickups (active) from Start date to End date
     *
     * @param CustomerShare $share
     */
    public function cutPickups(CustomerShare $share)
    {
        $activeNum = 0;

        /** @var Pickup $pickup */
        foreach ($share->getPickups() as $pickup) {
            // If counter equals to needed pickups number (not skipped), remove all forward pickups (active/skipped/suspended)
            if ($activeNum < $share->getPickupsNum()) {
                // Count active pickups from beginning to the needed pickups number
                if (!$pickup->isSkipped() && !$pickup->isSuspended()) $activeNum++;
            } else {
                // Remove all pickups after needed number
                $share->removePickup($pickup);
            }
        }
    }

    /**
     * Get pickup dates from the start date to the renewal date with correction to a pickup date,
     * if client set wrong pickup day and pickup date (not equal dates, day of week is not equal to start date)
     *
     * @param CustomerShare $share
     * @return array
     */
    public function getShareDates(CustomerShare $share)
    {
        $dates = [];

        // Find first pickup date with correction to correct day of week
        $dates[0] = $this->getNextPickupDate($share,'+', 0, $share->getStartDate());

        // If first pickup date is past, set start date to first pickup date
        if ($dates[0] < $share->getStartDate()) $share->setStartDate($dates[0]);

        // Find 1+ next pickup dates, each 7 days from first date
        for ($i = 1; $i < $share->getPickupsNum(); $i++) {
            $dates[$i] = $this->getNextPickupDate($share,'+', 7, $dates[$i - 1]);
        }

        return $dates;
    }

    /**
     * Get date of next pickup from given date
     *
     * @param CustomerShare $share
     * @param $operator
     * @param $days
     * @param $fromDate
     * @return \DateTime
     */
    public function getNextPickupDate(CustomerShare $share, $operator, $days, $fromDate)
    {
        $date = new \DateTime();
        $date->setTimestamp(strtotime($operator . $days . ' day', strtotime($fromDate->format('Y-m-d'))));

        // Get day of week from date
        $dateWeek = $date->format('w');

        // Change date week number from 0 to 7 if Sunday
        if ($dateWeek == 0) $dateWeek = 7;

        // If date is not equal to pickup day, move date to closest pickup date
        if ($dateWeek != $share->getPickUpDay()) {
            //$operator = $dateWeek < $share->getPickUpDay() ? 'next ' : 'last ';
            $date = new \DateTime(date('Y-m-d', strtotime('next ' . $share->getShareDay(), strtotime($fromDate->format('Y-m-d')))));
        }

        return $date;
    }

    /**
     * Update renewal date to last pickup date and update share status
     *
     * @param CustomerShare $share
     */
    public function updateShareRenewalDate(CustomerShare $share)
    {
        $lastPickupDate = null;

        /** @var Pickup $pickup */
        foreach ($share->getPickups() as $pickup) {
            $date = $pickup->getDate()->format('Y-m-d');
            if ($date > $lastPickupDate) $lastPickupDate = $date;
        }

        if ($share->getPickupsNum() == 0) $lastPickupDate = $share->getStartDate()->format('Y-m-d');

        $share->setRenewalDate(new \DateTime($lastPickupDate));
        $this->updateStatus($share);
    }

    /**
     * Define share status based on share start date and renewal date (end date)
     *
     * If renewal date is future and start date is past (already date is less than now) or start date is future,
     * but only 7 days or less lefts to the start date - status is ACTIVE
     *
     * If renewal date is future and start date is future and days to start date more than 7 - status is PENDING
     * If renewal date is past -> set status to LAPSED
     *
     * @param CustomerShare $share
     */
    public function updateStatus(CustomerShare $share)
    {
        $now = new \DateTime("midnight");

        // If renewal date is the future -> set ACTIVE or PENDING, else set to LAPSED or RENEWAL
        if ($share->getRenewalDate() >= $now) {
            $daysToActive = $this->countDaysLeft($share->getStartDate());

            // If start date is less than now or days to start date lefts only 7 or less set ACTIVE, else PENDING
            if ($share->getStartDate() <= $now || $daysToActive <= 7) {
                $share->setStatusByName('ACTIVE');
            } else {
                $share->setStatusByName('PENDING');
            }
        } else {
            $share->setStatusByName('LAPSED');
        }
    }

    /**
     * @param Client $client
     * @param null $week
     * @return bool
     * @throws \Exception
     */
    public function isWeekSuspended(Client $client, $week = null) : bool
    {
        $now = new \DateTime("midnight");

        // If week is not defined set week number to current
        if (!$week) {
            $weekDay = $now->format('w');

            // If today is Sunday, set week number by tomorrow (in West World - Sunday is the first day of week)
            $week = $weekDay != 0 ? $now->format('W') : $now->modify('+1 day')->format('W');
        }

        $suspendedWeek = $this->em->getRepository(SuspendedWeek::class)->findOneBy([
            'client' => $client,
            'week' => $week,
            'year' => $now->format('Y')
        ]);

        return $suspendedWeek ? true : false;
    }

    /**
     * Count number of days to date
     *
     * @param $date
     * @return false|float|int
     * @throws \Exception
     */
    public function countDaysLeft($date)
    {
        $startDate = strtotime(date_format($date, 'Y-m-d H:i:s'));
        $now = strtotime(date_format(new \DateTime("midnight"), 'Y-m-d H:i:s'));

        $diffInDays = floor(($startDate - $now) / (60 * 60 * 24));

        return $diffInDays + 1;
    }

    /**
     * Get pickups that lefts for the share in format: Share id => pickups
     *
     * @param Customer $member
     * @param CustomerShare|null $share
     * @return array
     */
    public function getFollowingPickups(Customer $member, CustomerShare $share = null)
    {
        return $this->em->getRepository(Pickup::class)->getFollowingPickups($member, $share);
    }

    /**
     * Update customer without sending of notifications
     *
     * @param Customer $member
     */
    public function update(Customer $member)
    {
        $this->updatePickups($member);

        if (!count($member->getNotifications())) {
            $this->activateNotifications($member);
        }

        if ($member->getIsLead() && count($member->getShares())) $member->setIsLead(false);

        $this->em->persist($member);
        $this->em->flush();
    }

    /**
     * @param Client $client
     * @param $shareDay
     * @param $shareDate
     * @return \Doctrine\Common\Collections\Collection|CustomerShare[] $shares | null
     */
    public function getSharesByShareDay(Client $client, $shareDay, $shareDate)
    {
        return $this->em->getRepository(CustomerShare::class)->searchByShareDay($client, $shareDay, $shareDate);
    }

    /**
     * @param Share $share
     */
    public function createShare(Share $share)
    {
        $this->em->persist($share);
        $this->em->flush();
    }

    /**
     * @param Location $location
     */
    public function createLocation(Location $location)
    {
        $this->em->persist($location);
        $this->em->flush();
    }

    /**
     * @param Location $location
     */
    public function removeLocation(Location $location)
    {
        $this->em->remove($location);
        $this->em->flush();
    }

    /**
     * @param Location $location
     */
    public function updateLocation(Location $location)
    {
        $this->em->flush();
    }

    /**
     * @param Client $client
     * @param null $isActive
     * @return Share[]|\Doctrine\Common\Collections\Collection
     */
    public function getShares(Client $client, $isActive = null)
    {
        return $this->em->getRepository(Share::class)->getShares($client, $isActive);
    }

    /**
     * @param Client $client
     * @return Location[]|\Doctrine\Common\Collections\Collection
     */
    public function getActiveLocations(Client $client)
    {
        return $this->em->getRepository(Location::class)->getLocationsWorkdays($client);
    }

    /**
     * @param Client $client
     * @param $sharesEnabled
     * @return array
     */
    public function getLocations(Client $client, $sharesEnabled)
    {
        return $this->em->getRepository(Location::class)->getLocations($client, $sharesEnabled);
    }

    /**
     * @param $id
     * @return Location|null|object
     */
    public function getLocationById($id)
    {
        return $this->em->find(Location::class, $id);
    }

    /**
     * @param Client $client
     * @param $day
     * @return array
     */
    public function getLocationsByDay(Client $client, $day)
    {
        return $this->em->getRepository(Location::class)->getLocationsByDay($client, $day);
    }

    /**
     * @param Client $client
     * @return array|bool
     */
    public function getClientWorkdays(Client $client)
    {
        return $this->em->getRepository(Workday::class)->getWorkdays($client);
    }

    /**
     * @param $member
     * @return null|string
     */
    public function getMemberStatus(Customer $member)
    {
        $status = null;

        if ($member->getIsLead()) {
            $status = 'lead';
        } else {
            // If customer have any shares -> he is MEMBER OR PATRON
            if (count($member->getShares()) || count($member->getOrders())) {
                $status = 'patron';

                // If customer have at least one MEMBER share, set status to MEMBER and break;
                foreach ($member->getShares() as $share) {
                    if ($share->getTypeName() == 'MEMBER') {
                        $status = 'customer';
                        break;
                    }
                }
            } else {
                $status = 'contact';
            }
        }

        return $status;
    }

    /**
     * @param CustomerShare $share
     * @return int
     */
    public function countPickups(CustomerShare $share)
    {
        $counter = 0;

        $now = new \DateTime('midnight');

        foreach ($share->getPickups() as $pickup) {
            if ($pickup->getDate() >= $now && !$pickup->isSkipped() && !$pickup->isSuspended()) $counter++;
        }

        return $counter;
    }

    /**
     * Function for controlling suspended weeks - suspend/unSuspend
     *
     * @param Client $client
     * @param $year
     * @param $week
     * @return SuspendedWeek|\Exception|null|object|\Throwable
     */
    public function suspendWeek(Client $client, $year, $week)
    {
        $this->em->getConnection()->beginTransaction();

        try {
            $suspendedWeek = $this->findSuspendedWeek($client, $year, $week);

            // Suspend or unSuspend week
            if (!$suspendedWeek) {
                // Create record on suspend week table
                $suspendedWeek = new SuspendedWeek();
                $suspendedWeek->setClient($client);
                $suspendedWeek->setWeek($week);
                $suspendedWeek->setYear($year);
                $this->em->persist($suspendedWeek);

                // Get all client pickups for suspended week
                $weekPickups = $this->em->getRepository(Pickup::class)->getWeekPickups($client, $year, $week);

                // Set pickup as suspended and manually add extra pickup, if week not skipped by customer
                foreach ($weekPickups as $pickup) {
                    // Set pickup as suspended and manually add extra pickup, if week not skipped by customer
                    $this->suspendPickup($pickup);
                }
            } else {
                $suspendedPickups = $this->em->getRepository(Pickup::class)->getSuspendedPickups($client, $year, $week);

                // Remove "suspended" status from pickup and manually remove extra pickup, if week not skipped by customer
                foreach ($suspendedPickups as $pickup) {
                    $this->unSuspendPickup($pickup);
                }

                // Remove suspended week
                $this->em->remove($suspendedWeek);
            }

            $this->em->flush();
            $this->em->getConnection()->commit();

            $response = true;
        } catch (\Throwable $e) {
            $this->em->getConnection()->rollBack();

            $response = $e;
        }

        return $response;
    }

    /**
     * @param Client $client
     * @param $year
     * @param $week
     * @return SuspendedWeek|null|object
     */
    public function findSuspendedWeek(Client $client, $year, $week)
    {
        $suspendedWeek = $this->em->getRepository(SuspendedWeek::class)->findOneBy([
            'client' => $client,
            'week' => $week,
            'year' => $year
        ]);

        return $suspendedWeek;
    }

    /**
     * Set pickup as suspended. Add extra pickup and update renewal date.
     *
     * @param Pickup $pickup
     * @return Pickup|null
     */
    public function suspendPickup(Pickup $pickup)
    {
        $pickup->setIsSuspended(true);

        // Add extra pickup for not skipped pickups
        if (!$pickup->isSkipped()) {
            $share = $pickup->getShare();

            // Add new pickup to the end of pickups list
            $newPickup = new Pickup();
            $newPickup->setDate($share->getRenewalDate()->modify('+7 days'));
            $share->addPickup($newPickup);

            // Update share renewal date
            $this->updateShareRenewalDate($share);

            return $newPickup;
        }

        return null;
    }

    /**
     * unSuspend pickup. Remove extra pickup and update renewal date.
     *
     * @param Pickup $pickup
     */
    public function unSuspendPickup(Pickup $pickup)
    {
        $pickup->setIsSuspended(false);

        // Remove extra pickup for not skipped pickup
        if (!$pickup->isSkipped()) {
            $share = $pickup->getShare();

            // Update share renewal date
            $this->cutPickups($share);

            // Update share renewal date
            $this->updateShareRenewalDate($share);
        }
    }

    /**
     * @param Client $client
     * @return array
     */
    public function getSuspendedWeeks(Client $client)
    {
        $weeks = $this->em->getRepository(SuspendedWeek::class)->getSuspendedWeeks($client);

        return $weeks;
    }

    /**
     * @param Client $client
     * @param $status
     * @param $search
     * @return \Doctrine\ORM\Query
     */
    public function searchCustomers(Client $client, $status = 'all', $search = '')
    {
        $customers = [];

        switch ($status) {
            case 'all':
                $customers = $this->repository->searchByAll($client, $search);
                break;
            case 'leads':
                $customers = $this->repository->searchByLeads($client, $search);
                break;
            case 'contacts':
                $customers = $this->repository->searchByContacts($client, $search);
                break;
            case 'members':
                $customers = $this->repository->searchByMembers($client, $search);
                break;
            case 'patrons':
                $customers = $this->repository->searchByPatrons($client, $search);
                break;
        }

        return $customers;
    }

    /**
     * @param Client $client
     * @param $search
     * @return array
     */
    public function searchByAllCustomers(Client $client, $search)
    {
        $customers = $this->repository->searchByAll($client, $search)->getResult();

        return $customers;
    }

    /**
     * @param Client $client
     * @param null $email
     * @param null $phone
     * @return null|object
     */
    public function findOneByEmailOrPhone(Client $client, $email = null, $phone = null)
    {
        if ($email) {
            $member = $this->repository->findOneBy(['client' => $client, 'email' => $email]);
        } else {
            $countryInfo = new CountryInfo();
            $phone = $countryInfo->getUnmaskedPhone($phone, $client->getCountry());
            $member = $this->repository->findOneBy(['client' => $client, 'phone' => $phone]);
        }

        return $member;
    }

    /**
     * @param $id
     * @return null|object
     */
    public function findOneById($id)
    {
        return $this->repository->find($id);
    }

    /**
     * @param Client $client
     * @param $data
     * @return null|object
     */
    public function findCustomerByData(Client $client, $data)
    {
        $member = null;

        if (isset($data['id'])) {
            $member = $this->findOneById($data['id']);
        } elseif ($data['email'] || $data['phone']) {
            $member = $this->findOneByEmailOrPhone($client, $data['email'], $data['phone']);
        }

        return $member;
    }

    /**
     * @param Client $client
     * @param array|int $categories
     * @return array
     */
    public function getAvailableProducts(Client $client, $categories)
    {
        if ($categories && !array($categories)) $categories = [$categories];

        $products = $this->getProducts($client, $categories);

        $availableProducts = [];

        foreach ($products as $product) {
            $availableProducts[] = $product;
        }

        return $availableProducts;
    }

    /**
     * @param Client $client
     * @param null $category
     * @return array
     */
    public function getProducts(Client $client, $category = null)
    {
        return $this->em->getRepository(Product::class)->getClientProducts($client, $category);
    }

    /**
     * Here we remove share and save last share day form share to customer delivery day
     *
     * @param CustomerShare $share
     */
    public function deleteShare(CustomerShare $share)
    {
        $share->getMember()->setDeliveryDay($share->getPickUpDay());

        $this->em->remove($share);
        $this->em->flush();
    }

    /**
     * @param Client $client
     * @param $emails
     * @return array
     */
    public function findEmailsMatch(Client $client, $emails)
    {
        $emails = $this->repository->findEmailsMatch($client, $emails);

        return $emails;
    }

    /**
     * @param Client $client
     * @param $status - All/Paid/Unpaid
     * @return \Doctrine\ORM\Query
     */
    public function getCustomersInvoices(Client $client, $status)
    {
        return $this->em->getRepository(Invoice::class)->getInvoices($client, $status);
    }

    /**
     * @param Client $client
     * @return \App\Entity\Customer\Product[]|\Doctrine\Common\Collections\Collection
     */
    public function getCustomerProducts(Client $client)
    {
        return $this->em->getRepository(Product::class)->getCustomerProducts($client);
    }

    /**
     * @param Customer $contact
     * @throws \Exception
     */
    public function subscribeMailchimpContact(Customer $contact)
    {
        $apiKey = '57ed0f38a62cf7b30e17c6c98082ef30-us20';
        $listId = 'e96449a84a';

        $data = [
            'email_address' => $contact->getEmail(),
            'status' => 'subscribed',
        ];

        if (strlen($contact->getFirstname()) > 0) $data['merge_fields']['FNAME'] = $contact->getFirstname();
        if (strlen($contact->getLastname()) > 0) $data['merge_fields']['LNAME'] = $contact->getLastname();

        $mailchimp = new MailChimp($apiKey);
        $mailchimp->post('lists/' . $listId . '/members', $data);

        if (!$mailchimp->success()) {
            throw new \Exception($mailchimp->getLastError());
        }
    }

    /**
     * @param Customer $member
     */
    public function removeMember(Customer $member)
    {
        $this->em->remove($member);
        $this->em->flush();
    }

    /**
     * @param Client $client
     * @return array
     */
    public function getPaymentSettings(Client $client)
    {
        $results = $this->em->getRepository(PaymentSettings::class)->findBy(['client' => $client]);

        $settings = [];

        foreach ($results as $setting) {
            $settings[$setting->getMethod()] = $setting;
        }

        return $settings;
    }
}