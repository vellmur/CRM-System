<?php

namespace App\Manager;

use App\Data\CountryInfo;
use App\Entity\Customer\Apartment;
use App\Entity\Customer\CustomerEmailNotify;
use App\Entity\Customer\Email\AutoEmail;
use App\Entity\Customer\Product;
use App\Repository\MemberRepository;
use App\Entity\Client\Client;
use App\Entity\Customer\Customer;
use Doctrine\ORM\EntityManagerInterface;

class MemberManager
{
    private $em;

    private $repository;

    public function __construct(EntityManagerInterface $em, MemberRepository $repository)
    {
        $this->em = $em;
        $this->repository = $repository;
    }

    /**
     * @param Customer $customer
     * @return Customer
     * @throws \Exception
     */
    public function addCustomer(Customer $customer)
    {
        $now = new \DateTime();
        $customer->setToken($customer->getFullname() . $now->format('Y-m-d H:i:s'));

        $this->activateNotifications($customer);

        $this->em->persist($customer);
        $this->em->flush();

        return $customer;
    }

    /**
     * @param Customer $member
     */
    public function activateNotifications(Customer $member)
    {
        foreach (AutoEmail::EMAIL_TYPES as $key => $type) {
            $notify = new CustomerEmailNotify();
            $notify->setNotifyType($key);
            $this->em->persist($notify);

            $member->addNotification($notify);
        }
    }

    /**
     * Update customer without sending of notifications
     *
     * @param Customer $member
     */
    public function update(Customer $member)
    {
        if (!count($member->getNotifications())) {
            $this->activateNotifications($member);
        }

        $this->em->persist($member);
        $this->em->flush();
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
        return $this->repository->searchByAll($client, $search)->getResult();
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
            $phone = $countryInfo->getUnmaskedPhone($phone, $client->getAddress()->getCountry());
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
     * @param $emails
     * @return array
     */
    public function findEmailsMatch(Client $client, $emails)
    {
        return $this->repository->findEmailsMatch($client, $emails);
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
     * @param Customer $member
     */
    public function removeCustomer(Customer $member)
    {
        $this->em->remove($member);
        $this->em->flush();
    }
}