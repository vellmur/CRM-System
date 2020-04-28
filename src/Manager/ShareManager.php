<?php

namespace App\Manager;

use App\Entity\Client\Client;
use App\Entity\Customer\Invoice;
use App\Entity\Customer\Vendor;
use App\Entity\Customer\VendorOrder;
use Doctrine\ORM\EntityManagerInterface;

class ShareManager
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
     * @param Client $client
     * @return mixed
     */
    public function getVendorOrders(Client $client)
    {
        return $this->em->getRepository(VendorOrder::class)->getOrders($client);
    }

    /**
     * @param Client $client
     * @return array
     */
    public function getVendors(Client $client)
    {
        return $this->em->getRepository(Vendor::class)->getActiveVendors($client);
    }

    /**
     * @param Client $client
     * @return mixed
     */
    public function countOrders(Client $client)
    {
        return $this->em->getRepository(Invoice::class)->countOpenOrders($client);
    }

    /**
     * @param Client $client
     * @param $period
     * @return \Doctrine\ORM\Query
     */
    public function searchOpenOrders(Client $client, $period)
    {
        return  $this->em->getRepository(Invoice::class)->searchOpenOrders($client, $period);
    }
}