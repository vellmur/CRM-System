<?php

namespace App\Manager;

use App\Entity\Client\Client;
use App\Entity\Customer\Address;
use App\Entity\Customer\Contact;
use App\Entity\Customer\CustomerReferral;
use App\Entity\Customer\Invoice;
use App\Entity\Customer\Email\EmailRecipient;
use App\Entity\Customer\Feedback;
use App\Entity\Customer\Location;
use App\Entity\Customer\Customer;
use App\Entity\Customer\CustomerOrders;
use App\Entity\Customer\CustomerShare;
use App\Entity\Customer\Merchant;
use App\Entity\Customer\Pickup;
use App\Entity\Customer\RenewalView;
use App\Entity\Customer\Share;
use App\Entity\Customer\ShareProduct;
use App\Entity\Customer\TestimonialRecipient;
use App\Entity\Customer\Vendor;
use App\Entity\Customer\VendorOrder;
use App\Entity\Team;
use App\Entity\User\User;
use Doctrine\ORM\EntityManagerInterface;

class MembershipManager
{
    private $em;

    private $memberManager;

    public function __construct(EntityManagerInterface $em, MemberManager $manager)
    {
        $this->em = $em;
        $this->memberManager = $manager;
    }

    /**
     * @return MemberManager
     */
    public function getMemberManager()
    {
        return $this->memberManager;
    }

    public function flush()
    {
        $this->em->flush();
    }

    /**
     * @return \App\Entity\Client[]|array
     */
    public function getSoftwareClients()
    {
        $clients = $this->em->getRepository(Client::class)->getSoftwareClients();

        return $clients;
    }

    /**
     * Add/Renew customer membership for the invoice products (shares/products)
     *
     * Customer shares have 1 + N pickups (share dates)
     * Patron shares have just one pickup (one date).
     *
     * For customer shares we create pickups of ONE share based on qty.
     * For patron shares we create MULTIPLE different shares with one pickup of same type based on Qty.
     *
     * @param Invoice $invoice
     * @return Invoice
     */
    public function renewMembership(Invoice $invoice)
    {
        $member = $invoice->getMember();

        // Add/Renew customer shares and products
        foreach ($invoice->getItems() as $item) {
            // Add/Renew shares
            if ($item->getShare()) {
                $existedShare = $this->findCustomerShare($member, $item->getShare());

                // Renew existed share or add new share
                if ($existedShare) {
                    // If existed share have MEMBER type, just add N weeks to the share
                    if ($existedShare->getTypeName() == 'MEMBER') {
                        $this->renewShare($existedShare, $invoice->getLocation(), $item->getQty());
                    } else {
                        // If existed share have PATRON type, create N weeks of PATRON shares with one pickup
                        for ($i = 0; $i < $item->getQty(); $i++) {
                            $this->addNewShare($member, $invoice->getLocation(), $item->getShare(), 1);
                        }
                    }
                } else {
                    $this->addNewShare($member, $invoice->getLocation(), $item->getShare(), $item->getQty());
                }
            }
        }

        if (!$invoice->isPaid()) $invoice->setIsPaid(true);

        $this->memberManager->update($member);

        return $invoice;
    }

    /**
     * Reduce membership of customer. Cut N of share pickups
     *
     * @param Invoice $invoice
     * @return Invoice
     */
    public function reduceMembership(Invoice $invoice)
    {
        $member = $invoice->getMember();

        // Reduce qty of weeks/products for shares/products
        foreach ($invoice->getItems() as $item) {
            if ($item->getShare()) {
                $shares = $this->findCustomerShares($member, $item->getShare());

                if ($shares) {
                    // Use first share for defining a type
                    $customerShare = $shares[0];

                    // Reduce weeks num for customer or remove share for patron (Patron have one week in the product invoice)
                    if ($customerShare->getTypeName() == 'MEMBER') {
                        $weeksLeft = $customerShare->getPickupsNum() - $item->getQty();
                        $weeksLeft > 0 ? $customerShare->setPickupsNum($weeksLeft) : $this->em->remove($customerShare);
                    } else {
                        // Get patron shares from invoice products and remove them
                        for ($i = 0; $i < $item->getQty(); $i++) {
                            if (!isset($shares[$i])) break;

                            $this->em->remove($shares[$i]);
                        }
                    }
                }
            }
        }

        $invoice->setIsPaid(false);

        $this->memberManager->update($member);

        return $invoice;
    }

    /**
     * @param Customer $customer
     * @param Share $share
     * @return CustomerShare|null|object
     */
    public function findCustomerShare(Customer $customer, Share $share)
    {
        $share = $this->em->getRepository(CustomerShare::class)->findOneBy([
            'customer' => $customer,
            'share' => $share
        ]);

        return $share;
    }

    /**
     * @param Customer $customer
     * @param Share $share
     * @return CustomerShare[]|array
     */
    public function findCustomerShares(Customer $customer, Share $share)
    {
        $shares = $this->em->getRepository(CustomerShare::class)->findBy([
            'customer' => $customer,
            'share' => $share
        ]);

        return $shares;
    }

    /**
     * Add/Update customer data after signUp/Renewal action
     *
     * @param Customer $member
     * @param $data
     */
    public function saveMemberData(Customer $member, $data)
    {
        $orderDate = new \DateTime($data['orderDate']);
        $member->setDeliveryDay($orderDate->format('N'));

        // If customer set that billing address is not different from delivery, save address as 'BILLING AND DELIVERY'
        $extraBilling = isset($data['isNeedBilling']);
        $firstType = $extraBilling ? 'Delivery' : 'Billing and Delivery';

        // Save customer delivery address
        $this->saveAddress($member, $data['locationAddress'], $firstType);

        // Save customer billing address, if billing address is different
        if ($extraBilling && strlen($data['billingAddress']['street']) > 4) {
            $this->saveAddress($member, $data['billingAddress'], 'Billing');
        }

        // Update existed or add new customer
        $member->getId() ? $this->memberManager->update($member) : $this->memberManager->addMember($member);
    }

    /**
     * Add/Update address of given type (Delivery/Billing)
     *
     * @param Customer $member
     * @param $addressData
     * @param $type
     * @return Address|null
     */
    public function saveAddress(Customer $member, $addressData, $type)
    {
        $address = $member->getAddressByType($type);

        // Add new address if needed type is not exists
        if (!$address) {
            $address = new Address();
            $address->setType($address->getTypeId($type));
            $member->addAddress($address);
        }

        $address->setStreet($addressData['street']);
        $address->setApartment($addressData['apartment']);
        $address->setPostalCode($addressData['postalCode']);
        $address->setRegion($addressData['region']);
        $address->setCity($addressData['city']);

        return $address;
    }

    /**
     * @param Customer $member
     * @param Location $location
     * @param Share $share
     * @param $weeks
     * @return CustomerShare
     */
    public function addNewShare(Customer $member, Location $location, Share $share, $weeks)
    {
        $customerShare = new CustomerShare();
        $customerShare->setShare($share);
        $customerShare->setLocation($location);
        $customerShare->setStartDate(new \DateTime());
        $customerShare->setPickupsNum($weeks);

        // If customer purchased more than one share, set type to MEMBER, else save as PATRON
        $customerShare->setType($customerShare->getPickupsNum() > 1 ? 1 : 2);

        // Set share pickup date (share day) to customer delivery day or start date day of week
        if ($member->getDeliveryDay()) {
            $customerShare->setPickUpDay($member->getDeliveryDay());
        } else {
            $customerShare->setPickUpDay($customerShare->getStartDate()->format('w'));
        }

        $member->addShare($customerShare);

        return $customerShare;
    }

    /**
     * @param CustomerShare $customerShare
     * @param Location $location
     * @param $weeks
     * @return CustomerShare
     */
    public function renewShare(CustomerShare $customerShare, Location $location, $weeks)
    {
        $deliveryDay = $customerShare->getMember()->getDeliveryDay();

        // If delivery day of customer is changed, update share pickup day,
        if ($deliveryDay && $deliveryDay != $customerShare->getPickUpDay()) {
            $customerShare->setPickUpDay($deliveryDay);
        }

        $customerShare->setLocation($location);
        $customerShare->setPickupsNum($customerShare->getPickupsNum() + $weeks);

        return $customerShare;
    }

    /**
     * @param Pickup $pickup
     * @return \Doctrine\Common\Collections\Collection|Pickup[] $pickups|bool|\Exception
     */
    public function controlPickup(Pickup $pickup)
    {
        $this->em->getConnection()->beginTransaction();

        try {
            $share = $pickup->getShare();

            $pickup->setSkipped($pickup->isSkipped() ? false : true);

            if ($pickup->isSkipped()) {
                $newPickup = new Pickup();
                $newPickup->setDate($share->getRenewalDate()->modify('+7 days'));
                $share->addPickup($newPickup);

                $response[] = $newPickup;

                // Automatically updates of share renewal date and statuses based on pickups num, start date and renewal date
                $this->getMemberManager()->updateShareRenewalDate($share);

                // Get all suspended weeks by client
                $suspendedWeeks = $this->getMemberManager()->getSuspendedWeeks($share->getMember()->getClient());

                if ($suspendedWeeks) {
                    // Suspend client weeks and add to response suspended pickups
                    $suspendedPickups = $this->getMemberManager()->checkSuspendedWeeks($share, $suspendedWeeks);
                    if ($suspendedPickups) $response = array_merge($response, $suspendedPickups);
                }
            } else {
                // Save removed object for response
                $lastPickup = $this->em->getRepository(Pickup::class)->findOneBy(['share' => $share->getId(), 'skipped' => false], ['date' => 'desc']);
                $response[] = clone $lastPickup;

                $this->getMemberManager()->cutPickups($share);

                // Automatically updates of share renewal date and statuses based on pickups num, start date and renewal date
                $this->getMemberManager()->updateShareRenewalDate($share);
            }

            $this->em->flush();

            $this->em->getConnection()->commit();
        } catch (\Throwable $e) {
            $this->em->getConnection()->rollBack();

            $response = $e;
        }

        return $response;
    }

    /**
     * @param \Doctrine\Common\Collections\Collection|CustomerShare[] $customerShares
     * @return array
     */
    public function countPickups($customerShares)
    {
        $counter = ['total' => 0];

        $now = new \DateTime('midnight');

        foreach ($customerShares as $share) {
            if (!isset($counter[$share->getShare()->getId()])) $counter[$share->getShare()->getId()] = 0;

            foreach ($share->getPickups() as $pickup) {
                if ($pickup->getDate() < $now || $pickup->isSkipped() || $pickup->isSuspended()) continue;

                $counter[$share->getShare()->getId()] += 1;
                $counter['total'] += 1;
            }
        }

        return $counter;
    }

    /**
     * @param Customer $member
     * @return CustomerShare[]|array
     */
    public function getCustomerShares(Customer $member)
    {
        $shares = $this->em->getRepository(CustomerShare::class)->getMemberCurrentShares($member);

        return $shares;
    }

    /**
     * @param $token
     * @return null|Customer|object
     */
    public function findOneByToken($token)
    {
        $member = $this->em->getRepository(Customer::class)->findByToken($token);

        return $member;
    }

    /**
     * @param $token
     * @return null|Contact|object
     */
    public function findVendorContactByToken($token)
    {
        $member = $this->em->getRepository(Contact::class)->findOneBy(['token' => $token]);

        return $member;
    }

    /**
     * @param $token
     * @return Client|null|object
     */
    public function findClientByToken($token)
    {
        $client = $this->em->getRepository(Client::class)->findOneBy(['token' => $token]);

        return $client;
    }

    /**
     * @param Client $client
     * @param $methodId
     * @return \App\Entity\Customer\Merchant|null|object
     */
    public function gePaymentMerchant(Client $client, $methodId)
    {
        $merchant = $this->em->getRepository(Merchant::class)->findOneBy(['client' => $client, 'merchant' => $methodId]);

        return $merchant;
    }

    /**
     * @param Client $client
     * @return User
     */
    public function findFarmOwner(Client $client)
    {
        $owner = $this->em->getRepository(Team::class)->findOneBy(['client' => $client], ['user' => 'asc']);

        return $owner->getUser();
    }

    /**
     * @param $id
     * @return \App\Entity\Customer\Pickup|null|object
     */
    public function getPickup($id)
    {
        return $this->em->find(Pickup::class, $id);
    }

    /**
     * @param Client $client
     * @return \Doctrine\Common\Collections\Collection|CustomerShare[] $shares
     */
    public function getSharesToArchive(Client $client)
    {
        return $this->em->getRepository(CustomerShare::class)->getSharesToArchive($client);
    }

    /**
     * @param $id
     * @return Client|null|object
     */
    public function getClientById($id)
    {
        return $this->em->find(Client::class, $id);
    }

    /**
     * @param $email
     * @param Client $client
     * @return null|object|Customer
     */
    public function findOneByEmail($email, Client $client = null)
    {
        if ($client) {
            $member = $this->em->getRepository(Customer::class)->findOneBy(['client' => $client, 'email' => $email]);
        } else {
            $member = $this->em->getRepository(Customer::class)->findOneBy(['email' => $email]);
        }

        return $member;
    }

    /**
     * @param Customer $member
     * @return array
     */
    public function computeAddressesChange(Customer $member)
    {
        $uow = $this->em->getUnitOfWork();
        $uow->computeChangeSets(); // do not compute changes if inside a listener

        $changeSet = [];

        // Compute addresses changes
        foreach ($member->getAddresses() as $address) {
            $changeSet = $uow->getEntityChangeSet($address);
        }

        // If something changed, save in array addresses before and after
        if ($changeSet) {
            $addressesBefore = $this->em->getRepository(Address::class)->getDatabaseAddresses($member);

            $changeSet = [
                'before' => [],
                'after' => []
            ];

            // before
            foreach ($addressesBefore as $key => $address) {
                $changeSet['before'][$key] = $address;
            }

            // after
            foreach ($member->getAddresses() as $key => $address) {
                $changeSet['after'][$key] = $address;
            }
        }

        return $changeSet;
    }

    /**
     * @param Customer $member
     * @return array
     */
    public function getSharesProducts(Customer $member)
    {
        $shares = $this->em->getRepository(CustomerShare::class)->getSharesProducts($member);

        // Remove shares that are not in a date range in customer orders page
        /** @var CustomerShare $share */
        foreach ($shares as $key => $share) {
            $nextShare = $share->getPickups()[0];

            /** @var CustomerOrders $order */
            $order = $share->getShare()->getCustomerOrders()[0];

            $today = new \DateTime("midnight");
            $daysToNext = $today->diff($nextShare->getDate());

            // Remove customizing if end date of order is less than next share date or if last share date was 1 day ago
            if (($order->getEndDate() < $nextShare->getDate()) || $daysToNext->days == 6) {
                unset($shares[$key]);
            }
        }

        return $shares;
    }

    /**
     * @param VendorOrder $order
     */
    public function createVendorOrder(VendorOrder $order)
    {
        $this->em->persist($order);
        $this->em->flush();
    }

    /**
     * @param Vendor $vendor
     * @return VendorOrder[]|array
     */
    public function getVendorOrders(Vendor $vendor)
    {
        return $this->em->getRepository(VendorOrder::class)->getVendorOrder($vendor);
    }

    /**
     * @param $share
     * @param $role
     * @return array
     */
    public function getOrderProducts($share, $role)
    {
        return $this->em->getRepository(ShareProduct::class)->getOrderProducts($share, $role);
    }

    /**
     * @param EmailRecipient $recipient
     */
    public function setAsClicked(EmailRecipient $recipient)
    {
        $recipient->setIsClicked(true);
        $this->em->flush();
    }

    /**
     * @param Customer $customer
     * @param $shareId
     * @param $shareDate
     * @param $isSatisfied
     * @param EmailRecipient|null $recipient
     */
    public function saveFeedback(Customer $customer, $shareId, $shareDate, $isSatisfied, EmailRecipient $recipient = null)
    {
        $share = $this->em->find(Share::class, $shareId);
        $shareDate = new \DateTime($shareDate);

        $feedback = $this->em->getRepository(Feedback::class)->findOneBy(['customer' => $customer, 'share' => $share, 'shareDate' => $shareDate]);

        if (!$feedback) {
            $feedback = new Feedback();
            $feedback->setCustomer($customer);
            $feedback->setShare($share);
            $feedback->setShareDate($shareDate);
        }

        // Add email recipient if customer clicked on a feedback link from feedback email notification
        if ($recipient) {
            $feedback->setRecipient($recipient);
        }

        $feedback->setIsSatisfied($isSatisfied);
        $feedback->setCreatedAt(new \DateTime());

        $this->em->persist($feedback);
        $this->em->flush();
    }

    /**
     * @param Customer $member
     * @return mixed
     */
    public function getSharesFeedback(Customer $member)
    {
        return $this->em->getRepository(Pickup::class)->getSharesFeedback($member);
    }

    /**
     * @param $id
     * @return Invoice|null|object
     */
    public function getInvoice($id)
    {
        return $this->em->find(Invoice::class, $id);
    }

    /**
     * @param Invoice $invoice
     */
    public function removeInvoice(Invoice $invoice)
    {
        $this->em->remove($invoice);
        $this->em->flush();
    }

    /**
     * Save statistics for clicking on renewal steps (save views)
     *
     * @param $clientId
     * @param $customerId
     * @param $step
     */
    public function saveRenewalView($clientId, $customerId,  $step)
    {
        $client = $this->em->getRepository(Client::class)->find($clientId);
        $customer = $customerId ? $this->em->getRepository(Customer::class)->find($customerId) : null;

        $view = new RenewalView();
        $view->setClient($client);
        $view->setCustomer($customer);
        $view->setIp($_SERVER['REMOTE_ADDR']);
        $view->setStep($step);
        $view->setCreatedAt(new \DateTime());

        $this->em->persist($view);
        $this->em->flush();
    }

    /**
     * @return \Doctrine\Common\Collections\Collection|Client[] $clients
     */
    public function getClientsWithPatrons()
    {
        $patrons = $this->em->getRepository(Client::class)->getPOSPatrons();

        return $patrons;
    }

    /**
     * @param Customer $member
     * @param $email
     * @param $firstname
     * @param $lastname
     * @param $message
     * @return TestimonialRecipient|\PHPUnit\Framework\MockObject\Stub\Exception
     */
    public function createTestimonialRecipient(Customer $member, $email, $firstname, $lastname, $message)
    {
        try {
            $recipient = new TestimonialRecipient();
            $recipient->setAffiliate($member);
            $recipient->setEmail($email);
            $recipient->setFirstname($firstname);
            $recipient->setLastname($lastname);
            $recipient->setMessage($message);

            $this->em->persist($recipient);
            $this->em->flush();

            return $recipient;
        } catch (\Exception $exception) {
            $this->em->rollback();

            return throwException($exception);
        }
    }

    /**
     * @param $id
     * @return TestimonialRecipient|null|object
     */
    public function getTestimonialRecipientById($id)
    {
        $recipient = $this->em->find(TestimonialRecipient::class, $id);

        return $recipient;
    }

    /**
     * @param TestimonialRecipient $recipient
     * @return Customer|\PHPUnit\Framework\MockObject\Stub\Exception
     */
    public function createCustomerFromTestimonial(TestimonialRecipient $recipient)
    {
        $this->em->getConnection()->beginTransaction();

        try {
            $customer = new Customer();
            $customer->setClient($recipient->getAffiliate()->getClient());
            $customer->setFirstname($recipient->getFirstname());
            $customer->setLastname($recipient->getLastname());
            $customer->setEmail($recipient->getEmail());

            $member = $this->memberManager->addMember($customer);

            $referral = new CustomerReferral();
            $referral->setCustomer($recipient->getAffiliate());
            $referral->setReferral($customer);

            $this->em->persist($referral);
            $this->em->flush();

            $this->em->commit();

            return $member;
        } catch (\Exception $exception) {
            $this->em->rollback();

            return throwException($exception);
        }
    }
}
