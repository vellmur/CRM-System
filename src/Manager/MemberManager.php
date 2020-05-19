<?php

namespace App\Manager;

use App\Entity\Customer\Apartment;
use App\Entity\Customer\CustomerEmailNotify;
use App\Entity\Customer\Email\AutoEmail;
use App\Repository\MemberRepository;
use App\Entity\Building\Building;
use App\Entity\Customer\Customer;
use App\Service\Localization\PhoneFormat;
use App\Service\Localization\PhoneFormatter;
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
     * @param Building $building
     * @param Customer $customer
     * @return Customer
     * @throws \Exception
     */
    public function addCustomer(Building $building, Customer $customer)
    {
        $now = new \DateTime();
        $customer->setToken($customer->getFullName() . $now->format('Y-m-d H:i:s'));
        $customer->setBuilding($building);

        $this->activateNotifications($customer);

        $this->em->persist($customer);
        $this->em->flush();

        return $customer;
    }

    /**
     * @param Building $building
     * @param string|null $apartmentNumber
     * @return Apartment|object|null
     */
    public function findOrCreateApartment(Building $building, ?string $apartmentNumber)
    {
        $rep = $this->em->getRepository(Apartment::class);

        if ($apartmentNumber !== null && $apartment = $rep->findOneBy(['building' => $building, 'number' => trim($apartmentNumber)])) {
            /** @var Apartment $apartment */
            return $apartment;
        }

        $apartment = new Apartment();
        $apartment->setBuilding($building);

        return $apartment;
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
     * @param Building $building
     * @param $status
     * @param $search
     * @return \Doctrine\ORM\Query
     */
    public function searchCustomers(Building $building, $status = 'all', $search = '')
    {
        $customers = [];

        switch ($status) {
            case 'all':
                $customers = $this->repository->searchByAll($building, $search);
                break;
            case 'leads':
                $customers = $this->repository->searchByLeads($building, $search);
                break;
            case 'contacts':
                $customers = $this->repository->searchByContacts($building, $search);
                break;
            case 'members':
                $customers = $this->repository->searchByMembers($building, $search);
                break;
            case 'patrons':
                $customers = $this->repository->searchByPatrons($building, $search);
                break;
        }

        return $customers;
    }

    /**
     * @param Building $building
     * @param $search
     * @return array
     */
    public function searchByAllCustomers(Building $building, $search)
    {
        return $this->repository->searchByAll($building, $search)->getResult();
    }

    /**
     * @param Building $building
     * @param null $email
     * @param null $phone
     * @return object|null
     * @throws \Exception
     */
    public function findOneByEmailOrPhone(Building $building, $email = null, $phone = null)
    {
        if ($email) {
            $member = $this->repository->findOneBy(['building' => $building, 'email' => $email]);
        } else {
            $phoneFormat = new PhoneFormat($building->getAddress()->getCountry());
            $phoneFormatter = new PhoneFormatter($phoneFormat, $phone);
            $phone = $phoneFormatter->getClearPhoneNumber();
            $member = $this->repository->findOneBy(['building' => $building, 'phone' => $phone]);
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
     * @param Building $building
     * @param $data
     * @return object|null
     * @throws \Exception
     */
    public function findCustomerByData(Building $building, $data)
    {
        $member = null;

        if (isset($data['id'])) {
            $member = $this->findOneById($data['id']);
        } elseif ($data['email'] || $data['phone']) {
            $member = $this->findOneByEmailOrPhone($building, $data['email'], $data['phone']);
        }

        return $member;
    }

    /**
     * @param Building $building
     * @param $emails
     * @return array
     */
    public function findEmailsMatch(Building $building, $emails)
    {
        return $this->repository->findEmailsMatch($building, $emails);
    }

    /**
     * @param Building $building
     * @return mixed
     */
    public function getCustomerProducts(Building $building)
    {
        return $this->em->getRepository(Product::class)->getCustomerProducts($building);
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