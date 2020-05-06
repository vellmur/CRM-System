<?php

namespace App\Tests\Entity\User;

use App\Entity\Client\Client;
use App\Entity\Customer\Customer;
use App\Entity\Customer\CustomerEmailNotify;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;

class CustomerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function testCustomerCreate()
    {
        $customer = new Customer();
        $date = new \DateTime();
        $client = new Client();

        $customer->setId(1);
        $customer->setEmail('johngolt@gmail.com');
        $customer->setFirstname('John');
        $customer->setLastname('Golt');
        $customer->setClient($client);
        $customer->setPhone('+380936069590');
        $customer->setCreatedAt($date);

        $this->assertNull($customer->getToken());
        $customer->setToken('wewewewewewewew');
        $this->assertNotNull($customer->getToken());
        $this->assertIsString($customer->getToken());

        $this->assertEquals(1, $customer->getId());
        $this->assertEquals('johngolt@gmail.com', $customer->getEmail());
        $this->assertEquals('JOHN', $customer->getFirstname());
        $this->assertEquals('GOLT', $customer->getLastname());
        $this->assertEquals('JOHN GOLT', $customer->getFullname());
        $this->assertEquals('johngolt@gmail.com', $customer->getEmail());
        $this->assertEquals('380936069590', $customer->getPhone());
        $this->assertEquals($client, $customer->getClient());
        $this->assertEquals($date, $customer->getCreatedAt());
        $this->assertEquals(false, $customer->isActivated());
        $customer->setIsActivated(true);
        $this->assertEquals(true, $customer->isActivated());

        $this->testSetClient();
        $this->testNotifications();
    }

    public function testSetClient()
    {
        $customer = new Customer();
        $client = new Client();

        $customer->setClient($client);

        $this->assertNotNull($client);
        $this->assertEquals($customer->getClient(), $client);
    }

    public function testNotifications()
    {
        $customer = new Customer();
        $this->assertEmpty($customer->getNotifications());

        $notification = new CustomerEmailNotify();
        $customer->addNotification($notification);
        $collection = new ArrayCollection([$notification]);

        $this->assertEquals($collection, $customer->getNotifications());
        $customer->setNotifications([]);
        $this->assertEquals([], $customer->getNotifications());

        $customer->setNotifications([$notification]);
        $this->assertEquals([$notification], $customer->getNotifications());

    }
}