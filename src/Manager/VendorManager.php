<?php

namespace App\Manager;

use App\Entity\Client\Client;
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
     * @param Client $client
     * @return array
     */
    public function getClientVendors(Client $client)
    {
        $vendors = $this->rep->findBy(['client' => $client], ['name' => 'asc']);

        return $vendors;
    }

    /**
     * @param Client $client
     * @param $search
     * @return array
     */
    public function searchVendors(Client $client, $search)
    {
        $vendors = $this->rep->searchByAllFields($client, $search);

        return $vendors;
    }
}