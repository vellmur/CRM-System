<?php

namespace App\Manager;

use App\Entity\Client\Client;
use App\Entity\Customer\Address;
use App\Entity\Customer\Contact;
use App\Entity\Customer\CustomerReferral;
use App\Entity\Customer\Invoice;
use App\Entity\Customer\Email\EmailRecipient;
use App\Entity\Customer\Customer;
use App\Entity\Customer\CustomerOrders;
use App\Entity\Customer\RenewalView;
use App\Entity\Customer\TestimonialRecipient;
use App\Entity\Customer\Vendor;
use App\Entity\Customer\VendorOrder;
use App\Entity\Client\Team;
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
     * Add/Update customer data after signUp/Renewal action
     *
     * @param Customer $member
     * @param $data
     */
    public function saveCustomerData(Customer $member, $data)
    {
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
     * @param $token
     * @return null|Customer|object
     */
    public function findOneByToken($token)
    {
        return $this->em->getRepository(Customer::class)->findByToken($token);
    }

    /**
     * @param $token
     * @return null|Contact|object
     */
    public function findVendorContactByToken($token)
    {
        return $this->em->getRepository(Contact::class)->findOneBy(['token' => $token]);
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
        return $this->em->getRepository(Client::class)->getPOSPatrons();
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
