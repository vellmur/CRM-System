<?php

namespace App\Manager;

use App\Entity\Client\Client;
use App\Entity\Customer\CustomShare;
use App\Entity\Customer\Invoice;
use App\Entity\Customer\Customer;
use App\Entity\Customer\CustomerOrders;
use App\Entity\Customer\CustomerShare;
use App\Entity\Customer\Pickup;
use App\Entity\Customer\Product;
use App\Entity\Customer\Share;
use App\Entity\Customer\ShareProduct;
use App\Entity\Customer\Vendor;
use App\Entity\Customer\VendorOrder;
use App\Repository\ShareProductsRepository;
use Doctrine\ORM\EntityManagerInterface;

class ShareManager
{
    private $em;

    private $rep;

    private $statuses = [
        'PENDING' => 1,
        'ACTIVE' => 2,
        'LAPSED' => 3
    ];

    /**
     * ShareManager constructor.
     * @param EntityManagerInterface $em
     * @param ShareProductsRepository $repository
     */
    public function __construct(EntityManagerInterface $em, ShareProductsRepository $repository)
    {
        $this->em = $em;
        $this->rep = $repository;
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
     * @param Share $share
     */
    public function removeShare(Share $share)
    {
        $this->em->remove($share);
        $this->em->flush();
    }

    /**
     * @param Client $client
     * @param $status
     * @param $share
     * @return array
     */
    public function searchSummaryShares(Client $client, $status, $share)
    {
        $share = $this->em->getRepository(Share::class)->findOneBy(['client' => $client, 'name' => $share]);

        $shareRepository = $this->em->getRepository(CustomerShare::class);

        switch ($status) {
            case 'total':
                $members = $shareRepository->getTotalShares($client, $share);
                break;
            case 'pending':
            case 'active':
            case 'lapsed':
                $status = $this->statuses[mb_strtoupper($status)];
                $members = $shareRepository->getSharesByStatus($client, $status, $share);
                break;
            case 'renewal':
                $members = $shareRepository->getRenewalShares($client, $share);
                break;
            case 'week':
                $members = $shareRepository->getNewMembers($client,7, $share);
                break;
            case 'month':
                $members = $shareRepository->getNewMembers($client,30, $share);
                break;
            default:
                $members = $shareRepository->getTotalShares($client, null);
        }

        return $members;
    }

    /**
     * @param $client
     * @return \App\Entity\Customer\CustomerShare[]|array
     */
    public function getCustomerSharesArray(Client $client)
    {
        $shares = $this->em->getRepository(CustomerShare::class)->getCustomerSharesArray($client);

        return $shares;
    }


    /**
     * @param Client $client
     * @return array
     */
    public function countShareMembers(Client $client)
    {
        $shareMembers = $this->em->getRepository(CustomerShare::class)->countShareMembers($client);

        return $shareMembers;
    }

    /**
     * @param Client $client
     * @return array
     */
    public function countShareMembersByStatus(Client $client)
    {
        $activeMembers = $this->em->getRepository(CustomerShare::class)->countShareMembersByStatus($client);

        return $activeMembers;
    }

    /**
     * @param Client $client
     * @return array
     */
    public function countRenewalMembers(Client $client)
    {
        $members = $this->em->getRepository(CustomerShare::class)->countRenewalMembers($client);

        return $members;
    }

    /**
     * @param Client $client
     * @param $days
     * @return array
     */
    public function countNewByDays(Client $client, $days)
    {
        $newMembersNum = $this->em->getRepository(CustomerShare::class)->countNewMembers($client, $days);

        return $newMembersNum;
    }

    /**
     * @param ShareProduct $shareProduct
     * @param $shareOrder
     * @param $role
     * @return ShareProduct
     */
    public function createProduct(ShareProduct $shareProduct, $shareOrder, $role)
    {
        if ($shareProduct->getWeight() != $shareProduct->getProduct()->getWeight()) {
            $shareProduct->setPrice($this->getTotalPrice($shareProduct));
        } else {
            $shareProduct->setPrice($shareProduct->getProduct()->getPrice());
        }

        $role == 'member' ? $shareProduct->setCustomerOrder($shareOrder) : $shareProduct->setVendorOrder($shareOrder);

        $this->em->persist($shareProduct);
        $this->em->flush();

        return $shareProduct;
    }

    /**
     * @param $share
     * @param $role
     * @return array
     */
    public function getOrderProducts($share, $role)
    {
        $orders = $this->rep->getOrderProducts($share, $role);

        return $orders;
    }

    /**
     *
     */
    public function updateShare()
    {
        $this->em->flush();
    }

    /**
     * @param $id
     * @param $role
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     */
    public function deleteShare($id, $role)
    {
        if ($role == 'member') {
            $share = $this->em->find(CustomerOrders::class, $id);
        } else {
            $share = $this->em->find(VendorOrder::class, $id);
        }

        $this->em->remove($share);
        $this->em->flush();
    }

    /**
     * @param ShareProduct $shareProduct
     */
    public function updateShareProduct(ShareProduct $shareProduct)
    {
        $shareProduct->setPrice($this->getTotalPrice($shareProduct));

        $this->em->flush();
    }

    /**
     * @param ShareProduct $shareProduct
     */
    public function deleteShareProduct(ShareProduct $shareProduct)
    {
        $this->em->remove($shareProduct);
        $this->em->flush();
    }

    /**
     * @param Client $client
     * @return \Doctrine\Common\Collections\Collection|CustomerOrders[] $orders | null
     */
    public function getCustomerOrders(Client $client)
    {
        $orders = $this->em->getRepository(CustomerOrders::class)->getOrders($client);

        return $orders;
    }

    /**
     * @param Client $client
     * @return \Doctrine\Common\Collections\Collection|VendorOrder[] $orders | null
     */
    public function getVendorOrders(Client $client)
    {
        $orders = $this->em->getRepository(VendorOrder::class)->getOrders($client);

        return $orders;
    }

    /**
     * @param $id
     * @return CustomShare|null
     */
    public function getCustomShareById($id)
    {
        if (strlen($id) > 0) {
            $share = $this->em->find(CustomShare::class, $id);
        } else {
            $share = null;
        }

        return $share;
    }

    /**
     * @param $id
     * @return ShareProduct|null|object
     */
    public function getShareProductById($id)
    {
        if (strlen($id) > 0) {
            $product = $this->em->find(ShareProduct::class, $id);
        } else {
            $product = null;
        }

        return $product;
    }

    /**
     * @param $client
     * @return \App\Entity\Customer\Share[]|array
     */
    public function getClientShares(Client $client)
    {
        $shares = $this->em->getRepository(Share::class)->findBy(['client' => $client], ['name' => 'ASC']);

        return $shares;
    }

    /**
     * @param ShareProduct $shareProduct
     * @return float
     */
    public function getTotalPrice(ShareProduct $shareProduct)
    {
        $product = $shareProduct->getProduct();
        $client = $shareProduct->getProduct()->getClient();

        $gramsInOne = $client->getWeightName() == 'Lbs' ? 453.592 : 1000;
        $gramPrice = $product->getPrice() / ($product->getWeight() * $gramsInOne);
        $totalPrice = $gramPrice * ($shareProduct->getWeight() * $gramsInOne);

        return number_format($totalPrice, 2, '.', '');
    }

    /**
     * @param $id
     * @param $role
     * @return \App\Entity\Customer\CustomerOrders|VendorOrder|null|object
     */
    public function getShareOrder($id, $role)
    {
        if ($role == 'member') {
            $share = $this->em->find(CustomerOrders::class, $id);
        } else {
            $share = $this->em->find(VendorOrder::class, $id);
        }

        return $share;
    }

    /**
     * @param $id
     * @return Share|null|object
     */
    public function getShare($id)
    {
        return $this->em->find(Share::class, $id);
    }

    /**
     * @param Customer $customer
     * @param Share $share
     * @return CustomerShare|null|object
     */
    public function getCustomerShare(Customer $customer, Share $share)
    {
        return $this->em->getRepository(CustomerShare::class)->findOneBy(['customer' => $customer, 'share' => $share]);
    }

    /**
     * @param $id
     */
    public function removeCustomShare($id)
    {
        $customShare = $this->em->find(CustomShare::class, $id);

        $this->em->remove($customShare);
        $this->em->flush();
    }

    /**
     * Product packaging list shows 7 closest pickups dates or vendor orders dates and counts total units of each plant,
     * for harvesting to needed date.
     *
     * Returns list for two entities Customer Order and Vendor Order.
     * Go through each customer pickup and count total units of all products in the order.
     * Then go through each vendor order day and add units num to products in the report.
     *
     * Returns array: [Date => [Product names => Total units]] for each pickup date and customer order date,
     * only 7 dates and sorted by key (pickup and orders dates).
     *
     * @param Client $client
     * @return array
     */
    public function getPackagingList(Client $client)
    {
        // First we beginning to get report list from customer orders by pickups
        $harvestPickups = $this->em->getRepository(Pickup::class)->getHarvestPickups($client);

        $report = [];

        // Get customer pickups for 7 days, count qty of each product in a share (Pickup date => Sum of products)
        foreach ($harvestPickups as $pickup) {
            // Create date key if not exists
            $date = $pickup->getDate()->format('Y-m-d');

            // Go through each product and count total units.
            foreach ($pickup->getPickupOrder()->getShareProducts() as $product) {
                // Create [date][product][product category] keys with values with count of total product units
                if (!isset($report[$date][$product->getName()][$product->getProduct()->getDescription()])) {
                    $report[$date][$product->getName()][$product->getProduct()->getDescription()] = 0;
                }

                $report[$date][$product->getName()][$product->getProduct()->getDescription()] += $product->getQty();
            }

            // Report need to show only next 7 pickup days
            if (count($report) == 7) break;
        }

        // Add to customer orders report -> vendor orders report
        $vendorsOrders = $this->em->getRepository(VendorOrder::class)->getVendorOrders($client);

        // Find vendors orders for closest 7 days and count qty of each product in order
        foreach ($vendorsOrders as $order) {
            $date = $order->getOrderDate()->format('Y-m-d');

            foreach ($order->getShareProducts() as $product) {
                if (!isset($report[$date][$product->getName()][$product->getProduct()->getDescription()])) {
                    $report[$date][$product->getName()][$product->getProduct()->getDescription()] = 0;
                }

                // Count total weight for date and plant
                $report[$date][$product->getName()][$product->getProduct()->getDescription()] += $product->getQty();
            }
        }

        // Sort report by dates
        ksort($report);

        // Only 7 dates must be max in report
        if (count($report) > 7) {
            $report = array_slice($report, 0, 7);
        }

        return $report;
    }


    /**
     * @param Client $client
     */
    public function deleteOldShares(Client $client)
    {
        $date = new \DateTime("midnight");

        $customerShares = $this->em->getRepository(CustomerOrders::class)->getOldShares($client, $date);

        $customerSharesCount = count($customerShares);

        if ($customerSharesCount > 0) {
            for ($i = 0; $i < $customerSharesCount; $i++) {
                $this->em->remove($customerShares[$i]);
            }
        }

        $vendorOrders = $this->em->getRepository(VendorOrder::class)->getOldShares($client, $date);

        $vendorOrdersCount = count($vendorOrders);

        if ($vendorOrdersCount > 0) {
            for ($i = 0; $i < $vendorOrdersCount; $i++) {
                $this->em->remove($vendorOrders[$i]);
            }
        }

        $this->em->flush();
    }

    /**
     * Customize share product using existed custom records or by creating new
     *
     * @param CustomerShare $share
     * @param $productId
     * @param CustomShare $customShare
     * @param ShareProduct $shareProduct
     * @return CustomShare
     */
    public function customizeShare(CustomerShare $share, $productId, CustomShare $customShare = null, ShareProduct $shareProduct = null)
    {
        $product = $this->em->find(Product::class, $productId);

        // If existed custom share found, just change product in this share, else -> add new custom record
        if ($customShare) {
            $customShare->setProduct($product);
        } else {
            $customShare = new CustomShare();
            $customShare->setProduct($product);
            $customShare->setShareProduct($shareProduct);
            $customShare->setShare($share);

            $this->em->persist($customShare);
        }

        $this->em->flush();

        return $customShare;
    }

    /**
     * @param Client $client
     * @return array
     */
    public function getVendors(Client $client)
    {
        $vendors = $this->em->getRepository(Vendor::class)->getActiveVendors($client);

        return $vendors;
    }

    /**
     * @param Client $client
     * @return mixed
     */
    public function countOrders(Client $client)
    {
        $orders = $this->em->getRepository(Invoice::class)->countOpenOrders($client);

        return $orders;
    }

    /**
     * @param Client $client
     * @param $period
     * @return \Doctrine\ORM\Query
     */
    public function searchOpenOrders(Client $client, $period)
    {
        $orders = $this->em->getRepository(Invoice::class)->searchOpenOrders($client, $period);

        return $orders;
    }
}