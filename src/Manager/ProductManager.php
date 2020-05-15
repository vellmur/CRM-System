<?php

namespace App\Manager;

use App\Entity\Building\Building;
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

        $this->createTags($product->getBuilding(), $product, $tags);
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
     * @param Building $building
     * @param null $category
     * @return array
     */
    public function getProducts(Building $building, $category = null)
    {
        $products = $this->rep->getBuildingProducts($building, $category);

        return $products;
    }

    /**
     * @param Building $building
     * @param $category
     * @return array
     */
    public function getProductsPricing(Building $building, $category)
    {
        $products = $this->rep->getProductsPricing($building, $category);

        return $products;
    }

    /**
     * @param Building $building
     * @param $category
     * @param $search
     * @return \Doctrine\ORM\Query
     */
    public function searchProducts(Building $building, $category = 'all', $search = '')
    {
        $customers = $this->rep->searchByAll($building, $category, $search);

        return $customers;
    }

    /**
     * @param Building $building
     * @param null $period
     * @return mixed
     */
    public function getPOSSummary(Building $building, $period = null)
    {
        $summary = $this->em->getRepository(POS::class)->getPOSSummary($building, $period);

        return $summary;
    }

    /**
     * @param Building $building
     * @param $search
     * @return array
     */
    public function searchByCustomers(Building $building, $search)
    {
        $customers = $this->em->getRepository(Customer::class)->searchByCustomers($building, $search)->getResult();

        return $customers;
    }

    /**
     * @param Building $building
     * @param string $search
     * @return \Doctrine\ORM\Query
     */
    public function searchPosProducts(Building $building, $search = '')
    {
        $customers = $this->rep->searchPOSProducts($building, $search);

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
     * @param Building $building
     * @param string $period
     * @return mixed
     */
    public function getPOSOrders(Building $building, $period = null)
    {
        $orders = $this->em->getRepository(POS::class)->getOrders($building, $period);

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
     * @param Building $building
     * @return array
     */
    public function getSalesStatistics(Building $building)
    {
        $stats = [
            'mostPurchased' => [],
            'dailySales' => [],
            'averageSale' => [],
            'hourSales' => []
        ];

        $sales = $this->em->getRepository(POS::class)->getSalesStatistics($building);

        foreach ($sales as $sale) {
            $date = new \DateTime($sale['date']);

            $stats['dailySales'][$date->format('d M')] = $sale['total'];
            $stats['averageSale'][$date->format('d M')] = number_format($sale['averageSale'],2);
        }

        $hourSales = $this->em->getRepository(POS::class)->getHourSales($building);

        foreach ($hourSales as $hourSale) {
            $stats['hourSales'][$hourSale['hour']] = number_format((float)$hourSale['total'], 2);
        }

        $mostPurchasedProducts = $this->em->getRepository(POS::class)->getMostPurchasedProducts($building);

        foreach ($mostPurchasedProducts as $product) {
            $stats['mostPurchased'][$product['name']] = $product['totalWeight'];
        }

        return $stats;
    }

    /**
     * @param Building $building
     * @return array
     */
    public function getMonthSales(Building $building)
    {
        $sales = $this->em->getRepository(POS::class)->getMonthSales($building);

        return $sales;
    }

    /**
     * @param Building $building
     * @param Product $product
     * @param $tags
     * @return Tag[]|\Doctrine\Common\Collections\Collection
     */
    public function createTags(Building $building, Product $product, $tags)
    {
        if (strlen($tags)) {
            $tags = array_unique(array_map('mb_strtoupper', explode(', ', $tags)));

            if (count($tags)) {
                $existedTags = $this->em->getRepository(Tag::class)->findTags($building, $tags);

                foreach ($tags as $tag) {
                    if (!in_array($tag, $existedTags)) {
                        $newTag = $this->createBuildingTag($tag);
                        $building->addTag($newTag);
                    }
                }

                foreach ($building->getTags() as $buildingTag) {
                    if (in_array($buildingTag->getName(), $tags)) {
                        $productTag = $this->addTag($product, $buildingTag);
                        $this->em->persist($productTag);
                    }
                }

                $this->em->flush();
            }
        }

        return $building->getTags();
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

        $buildingTags = $this->em->getRepository(Tag::class)->findTags($product->getBuilding(), $tags);

        foreach ($tags as $tag) {
            // If tag not added yet, add tags from building tags or create new building tags then add it
            if (!in_array($tag, $productTags)) {
                if (in_array($tag, $buildingTags)) {
                    $buildingTag = $this->em->find(Tag::class, array_search($tag, $buildingTags));
                    $productTag = $this->addTag($product, $buildingTag);
                } else {
                    $newTag = $this->createBuildingTag($tag);
                    $product->getBuilding()->addTag($newTag);

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
    public function createBuildingTag($name)
    {
        $tag = new Tag();
        $tag->setName($name);

        $this->em->persist($tag);

        return $tag;
    }
}