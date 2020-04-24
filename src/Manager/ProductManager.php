<?php

namespace App\Manager;

use App\Entity\Client\Client;
use App\Entity\Customer\Customer;
use App\Entity\Customer\POS;
use App\Entity\Customer\Product;
use App\Entity\Customer\ProductTag;
use App\Entity\Customer\Tag;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;

class ProductManager
{
    private $em;

    private $rep;

    /**
     * ProductManager constructor.
     * @param EntityManagerInterface $em
     * @param ProductRepository $repository
     */
    public function __construct(EntityManagerInterface $em, ProductRepository $repository)
    {
        $this->em = $em;
        $this->rep = $repository;
    }

    /**
     * @param Product $product
     * @param $tags string
     */
    public function createProduct(Product $product, $tags)
    {
        $product->setPayByItem(1);
        $this->em->persist($product);
        $this->em->flush();

        $this->createTags($product->getClient(), $product, $tags);
    }

    /**
     * @param Product $product
     * @param $tags string
     */
    public function updateProduct(Product $product, $tags)
    {
        $this->updateTags($product, $tags);
        $this->em->flush();
    }

    /**
     * @param Product $product
     */
    public function removeProduct(Product $product)
    {
        $this->em->remove($product);
        $this->em->flush();
    }

    public function flush()
    {
        $this->em->flush();
    }

    /**
     * @param Client $client
     * @param null $category
     * @return array
     */
    public function getProducts(Client $client, $category = null)
    {
        $products = $this->rep->getClientProducts($client, $category);

        return $products;
    }

    /**
     * @param Client $client
     * @param $category
     * @return array
     */
    public function getProductsPricing(Client $client, $category)
    {
        $products = $this->rep->getProductsPricing($client, $category);

        return $products;
    }

    /**
     * @param Client $client
     * @param $category
     * @param $search
     * @return \Doctrine\ORM\Query
     */
    public function searchProducts(Client $client, $category = 'all', $search = '')
    {
        $customers = $this->rep->searchByAll($client, $category, $search);

        return $customers;
    }

    /**
     * @param Client $client
     * @param null $period
     * @return mixed
     */
    public function getPOSSummary(Client $client, $period = null)
    {
        $summary = $this->em->getRepository(POS::class)->getPOSSummary($client, $period);

        return $summary;
    }

    /**
     * @param Client $client
     * @param $search
     * @return array
     */
    public function searchByCustomers(Client $client, $search)
    {
        $customers = $this->em->getRepository(Customer::class)->searchByCustomers($client, $search)->getResult();

        return $customers;
    }

    /**
     * @param Client $client
     * @param string $search
     * @return \Doctrine\ORM\Query
     */
    public function searchPosProducts(Client $client, $search = '')
    {
        $customers = $this->rep->searchPOSProducts($client, $search);

        return $customers;
    }

    /**
     * @param POS $pos
     * @return POS|\Exception
     */
    public function createPOSOrder(POS $pos)
    {
        try {
            foreach ($pos->getProducts() as $product) {
                $product->setPrice($product->getProduct()->getPrice());
                $product->setPos($pos);
            }

            $this->em->persist($pos);
            $this->em->flush();

            return $pos;
        } catch (\Exception $exception) {
            return $exception;
        }
    }

    /**
     * @param Client $client
     * @param string $period
     * @return mixed
     */
    public function getPOSOrders(Client $client, $period = null)
    {
        $orders = $this->em->getRepository(POS::class)->getOrders($client, $period);

        return $orders;
    }

    /**
     * @param POS $order
     */
    public function removePOSOrder(POS $order)
    {
        $this->em->remove($order);
        $this->em->flush();
    }

    /**
     * @param Client $client
     * @return array
     */
    public function getSalesStatistics(Client $client)
    {
        $stats = [
            'mostPurchased' => [],
            'dailySales' => [],
            'averageSale' => [],
            'hourSales' => []
        ];

        $sales = $this->em->getRepository(POS::class)->getSalesStatistics($client);

        foreach ($sales as $sale) {
            $date = new \DateTime($sale['date']);

            $stats['dailySales'][$date->format('d M')] = $sale['total'];
            $stats['averageSale'][$date->format('d M')] = number_format($sale['averageSale'],2);
        }

        $hourSales = $this->em->getRepository(POS::class)->getHourSales($client);

        foreach ($hourSales as $hourSale) {
            $stats['hourSales'][$hourSale['hour']] = number_format((float)$hourSale['total'], 2);
        }

        $mostPurchasedProducts = $this->em->getRepository(POS::class)->getMostPurchasedProducts($client);

        foreach ($mostPurchasedProducts as $product) {
            $stats['mostPurchased'][$product['name']] = $product['totalWeight'];
        }

        return $stats;
    }

    /**
     * @param Client $client
     * @return array
     */
    public function getMonthSales(Client $client)
    {
        $sales = $this->em->getRepository(POS::class)->getMonthSales($client);

        return $sales;
    }

    /**
     * @param Client $client
     * @param Product $product
     * @param $tags
     * @return Tag[]|\Doctrine\Common\Collections\Collection
     */
    public function createTags(Client $client, Product $product, $tags)
    {
        if (strlen($tags)) {
            $tags = array_unique(array_map('mb_strtoupper', explode(', ', $tags)));

            if (count($tags)) {
                $existedTags = $this->em->getRepository(Tag::class)->findTags($client, $tags);

                foreach ($tags as $tag) {
                    if (!in_array($tag, $existedTags)) {
                        $newTag = $this->createClientTag($tag);
                        $client->addTag($newTag);
                    }
                }

                foreach ($client->getTags() as $clientTag) {
                    if (in_array($clientTag->getName(), $tags)) {
                        $productTag = $this->addTag($product, $clientTag);
                        $this->em->persist($productTag);
                    }
                }

                $this->em->flush();
            }
        }

        return $client->getTags();
    }

    /**
     * @param Product $product
     * @param $tags
     */
    public function updateTags(Product $product, $tags)
    {
        $tags = array_unique(array_map('mb_strtoupper', explode(', ', $tags)));

        $productTags = $this->em->getRepository(ProductTag::class)->getTags($product);
        $removeTags = array_diff($productTags, $tags);

        // Remove tags from product does'nt exists anymore
        foreach ($removeTags as $id => $tag) {
            $productTag = $this->em->getReference(ProductTag::class, $id);
            $this->em->remove($productTag);
        }

        $clientTags = $this->em->getRepository(Tag::class)->findTags($product->getClient(), $tags);

        foreach ($tags as $tag) {
            // If tag not added yet, add tags from client tags or create new client tags then add it
            if (!in_array($tag, $productTags)) {
                if (in_array($tag, $clientTags)) {
                    $clientTag = $this->em->find(Tag::class, array_search($tag, $clientTags));
                    $productTag = $this->addTag($product, $clientTag);
                } else {
                    $newTag = $this->createClientTag($tag);
                    $product->getClient()->addTag($newTag);

                    $productTag = $this->addTag($product, $newTag);
                }

                $this->em->persist($productTag);
            }
        }

        $this->em->flush();
    }

    /**
     * @param Product $product
     * @param Tag $tag
     * @return ProductTag
     */
    public function addTag(Product $product, Tag $tag)
    {
        $productTag = new ProductTag();
        $productTag->setTag($tag);
        $productTag->setProduct($product);

        $this->em->persist($productTag);

        return $productTag;
    }

    /**
     * @param $name
     * @return Tag
     */
    public function createClientTag($name)
    {
        $tag = new Tag();
        $tag->setName($name);

        $this->em->persist($tag);

        return $tag;
    }
}