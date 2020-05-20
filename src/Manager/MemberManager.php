<?php

namespace App\Manager;

use App\Entity\Owner\Apartment;
use App\Entity\Owner\OwnerEmailNotify;
use App\Entity\Owner\Email\AutoEmail;
use App\Repository\MemberRepository;
use App\Entity\Building\Building;
use App\Entity\Owner\Owner;
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
     * @param Owner $owner
     * @return Owner
     * @throws \Exception
     */
    public function addOwner(Building $building, Owner $owner)
    {
        $now = new \DateTime();
        $owner->setToken($owner->getFullName() . $now->format('Y-m-d H:i:s'));
        $owner->setBuilding($building);

        $this->activateNotifications($owner);

        $this->em->persist($owner);
        $this->em->flush();

        return $owner;
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
     * @param Owner $member
     */
    public function activateNotifications(Owner $member)
    {
        foreach (AutoEmail::EMAIL_TYPES as $key => $type) {
            $notify = new OwnerEmailNotify();
            $notify->setNotifyType($key);
            $this->em->persist($notify);

            $member->addNotification($notify);
        }
    }

    /**
     * Update owner without sending of notifications
     *
     * @param Owner $member
     */
    public function update(Owner $member)
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
    public function searchOwners(Building $building, $status = 'all', $search = '')
    {
        $owners = [];

        switch ($status) {
            case 'all':
                $owners = $this->repository->searchByAll($building, $search);
                break;
            case 'leads':
                $owners = $this->repository->searchByLeads($building, $search);
                break;
            case 'contacts':
                $owners = $this->repository->searchByContacts($building, $search);
                break;
            case 'members':
                $owners = $this->repository->searchByMembers($building, $search);
                break;
            case 'patrons':
                $owners = $this->repository->searchByPatrons($building, $search);
                break;
        }

        return $owners;
    }

    /**
     * @param Building $building
     * @param $search
     * @return array
     */
    public function searchByAllOwners(Building $building, $search)
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
            $phoneFormatter = new PhoneFormatter($building->getAddress()->getCountry());
            $phone = $phoneFormatter->getCleanPhoneNumber($phone);
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
    public function findOwnerByData(Building $building, $data)
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
    public function getOwnerProducts(Building $building)
    {
        return $this->em->getRepository(Product::class)->getOwnerProducts($building);
    }

    /**
     * @param Owner $member
     */
    public function removeOwner(Owner $member)
    {
        $this->em->remove($member);
        $this->em->flush();
    }
}