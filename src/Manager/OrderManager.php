<?php

namespace App\Manager;

use App\Entity\Building\Building;
use App\Entity\Customer\Invoice;
use App\Entity\Customer\Vendor;
use App\Entity\Customer\VendorOrder;
use Doctrine\ORM\EntityManagerInterface;

class OrderManager
{
    private $em;

    /**
     * ShareManager constructor.
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @param $order
     */
    public function createOrder($order)
    {
        $this->em->persist($order);
        $this->em->flush();
    }

    /**
     * @param Building $building
     * @return mixed
     */
    public function getVendorOrders(Building $building)
    {
        return $this->em->getRepository(VendorOrder::class)->getOrders($building);
    }

    /**
     * @param Building $building
     * @return array
     */
    public function getVendors(Building $building)
    {
        return $this->em->getRepository(Vendor::class)->getActiveVendors($building);
    }

    /**
     * @param Building $building
     * @return mixed
     */
    public function countOrders(Building $building)
    {
        return $this->em->getRepository(Invoice::class)->countOpenOrders($building);
    }

    /**
     * @param Building $building
     * @param $period
     * @return \Doctrine\ORM\Query
     */
    public function searchOpenOrders(Building $building, $period)
    {
        return  $this->em->getRepository(Invoice::class)->searchOpenOrders($building, $period);
    }
}