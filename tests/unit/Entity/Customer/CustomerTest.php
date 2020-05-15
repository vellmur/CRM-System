<?php

namespace App\Tests\Entity\User;

use App\Entity\Building\Building;
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
        $building = new Building();

        $customer->setId(1);
        $customer->setEmail('johngolt@gmail.com');
        $customer->setFirstname('John');
        $customer->setLastname('Golt');
        $customer->setBuilding($building);
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
        $this->assertEquals($building, $customer->getBuilding());
        $this->assertEquals($date, $customer->getCreatedAt());
        $this->assertEquals(false, $customer->isActivated());
        $customer->setIsActivated(true);
        $this->assertEquals(true, $customer->isActivated());

        $this->testSetBuilding();
        $this->testNotifications();
    }

    public function testSetBuilding()
    {
        $customer = new Customer();
        $building = new Building();

        $customer->setBuilding($building);

        $this->assertNotNull($building);
        $this->assertEquals($customer->getBuilding(), $building);
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