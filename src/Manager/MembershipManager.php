<?php

namespace App\Manager;

use App\Entity\Client\Client;
use App\Entity\Customer\Contact;
use App\Entity\Customer\Invoice;
use App\Entity\Customer\Email\EmailRecipient;
use App\Entity\Customer\Customer;
use App\Entity\Customer\Vendor;
use App\Entity\Customer\VendorOrder;
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
     * @return \Doctrine\Common\Collections\Collection|Client[] $clients
     */
    public function getClientsWithPatrons()
    {
        return $this->em->getRepository(Client::class)->getPOSPatrons();
    }
}
