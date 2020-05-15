<?php

namespace App\Manager;

use App\Entity\Building\Building;
use App\Entity\Customer\Vendor;
use App\Repository\VendorRepository;
use Doctrine\ORM\EntityManagerInterface;

class VendorManager
{
    private $em;

    private $rep;

    public function __construct(EntityManagerInterface $em, VendorRepository $repository)
    {
        $this->em = $em;
        $this->rep = $repository;
    }

    /**
     * @param Vendor $vendor
     * @return int
     */
    public function addVendor(Vendor $vendor)
    {
        foreach ($vendor->getContacts() as $contact) {
            if (!$contact->getToken()) $contact->setToken($contact->getName() . ' ' . $contact->getEmail());
        }

        $this->em->persist($vendor);
        $this->em->flush();

        return $vendor->getId();
    }

    /**
     * @param Vendor $vendor
     */
    public function updateVendor(Vendor $vendor)
    {
        foreach ($vendor->getContacts() as $contact) {
            if (!$contact->getToken()) $contact->setToken($contact->getName() . ' ' . $contact->getEmail());
        }

        $this->em->flush();
    }

    /**
     * @param Vendor $vendor
     */
    public function removeVendor(Vendor $vendor)
    {
        $this->em->remove($vendor);
        $this->em->flush();
    }

    /**
     * @param Building $building
     * @return array
     */
    public function getBuildingVendors(Building $building)
    {
        $vendors = $this->rep->findBy(['building' => $building], ['name' => 'asc']);

        return $vendors;
    }

    /**
     * @param Building $building
     * @param $search
     * @return array
     */
    public function searchVendors(Building $building, $search)
    {
        $vendors = $this->rep->searchByAllFields($building, $search);

        return $vendors;
    }
}