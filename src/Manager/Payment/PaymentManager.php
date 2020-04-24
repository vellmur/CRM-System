<?php

namespace App\Manager\Payment;

use App\Entity\Customer\Invoice;
use App\Entity\Customer\InvoiceProduct;
use App\Entity\Customer\Location;
use App\Entity\Customer\Customer;
use App\Entity\Customer\Merchant;
use App\Entity\Client;
use App\Entity\Customer\Product;
use App\Entity\Customer\Share;
use App\Repository\PaymentRepository;
use Doctrine\ORM\EntityManagerInterface;

class PaymentManager
{
    private $em;

    private $paymentRepository;

    /**
     * PaymentManager constructor.
     * @param EntityManagerInterface $em
     * @param PaymentRepository $paymentRepository
     */
    public function __construct(EntityManagerInterface $em, PaymentRepository $paymentRepository)
    {
        $this->em = $em;
        $this->paymentRepository = $paymentRepository;
    }

    /**
     * @param Customer $customer
     * @param $data
     * @return Invoice
     * @throws \Throwable
     */
    public function createCustomerInvoice(Customer $customer, $data)
    {
        $this->em->getConnection()->beginTransaction();

        try {
            $invoice = new Invoice();
            $invoice->setMember($customer);
            $invoice->setLocation($this->em->find(Location::class, $data['location']));
            $invoice->setOrderDate(new \DateTime($data['orderDate']));

            $amount = 0;

            // Add shares to the invoice
            if (isset($data['shares'])) {
                foreach ($data['shares'] as $shareId) {
                    $product = $this->createInvoiceProduct($shareId, $data['shareQty'][$shareId], true);
                    $invoice->addItem($product);

                    $amount += $product->getAmount();
                }
            }

            // Add products to the invoice
            if (isset($data['products'])) {
                foreach ($data['products'] as $productId) {
                    $product = $this->createInvoiceProduct($productId, $data['productQty'][$productId], false);
                    $invoice->addItem($product);

                    $amount += $product->getAmount();
                }
            }

            if ($amount > 0) {
                // If delivery location is Home and total amount less than 12, add delivery amount to the total
                if ($amount < 12 && $invoice->getLocation()->isDelivery()) $amount += 12 - $amount;
                $invoice->setAmount($amount);

                $this->em->persist($invoice);
                $this->em->flush();
                $this->em->getConnection()->commit();

                return $invoice;
            } else {
                throw new \Exception('Total cart amount must be greater than $0.');
            }
        } catch (\Throwable $e) {
            $this->em->getConnection()->rollback();
            $this->em->clear();

            throw $e;
        }
    }

    /**
     * @param $productId
     * @param $qty
     * @param $isShare
     * @return InvoiceProduct
     */
    public function createInvoiceProduct($productId, $qty, $isShare)
    {
        $invoiceProduct = new InvoiceProduct();

        // Set share or product to the invoice product item
        if ($isShare) {
            $share = $this->em->find(Share::class, $productId);
            $invoiceProduct->setShare($share);
            $invoiceProduct->setQty($qty);
            $invoiceProduct->setAmount($share->getPrice() * $qty);
        } else {
            $product = $this->em->find(Product::class, $productId);
            $productPrice = $product->isPos() && $product->getDeliveryPrice() ? $product->getDeliveryPrice() : $product->getPrice();
            $product->isPayByItem() ? $invoiceProduct->setQty($qty) : $invoiceProduct->setWeight($qty);
            $invoiceProduct->setProduct($product);
            $invoiceProduct->setAmount($productPrice * $qty);
        }

        return $invoiceProduct;
    }

    /**
     * @param Invoice $invoice
     * @param $refNum
     * @return Invoice
     */
    public function completeCustomerPayment(Invoice $invoice, $refNum)
    {
        $invoice->setRefnum($refNum);
        $invoice->setIsPaid(true);

        $this->em->flush();

        return $invoice;
    }

    /**
     * @param Client $client
     * @return Merchant|null|object
     */
    public function getUSAePayMerchant(Client $client)
    {
        return $this->em->getRepository(Merchant::class)->findOneBy(['client' => $client, 'merchant' => 1]);
    }
}