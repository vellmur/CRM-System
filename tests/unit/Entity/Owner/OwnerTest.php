<?php

namespace App\Tests\Entity\User;

use App\Entity\Building\Building;
use App\Entity\Owner\Owner;
use App\Entity\Owner\OwnerEmailNotify;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;

class OwnerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function testOwnerCreate()
    {
        $owner = new Owner();
        $date = new \DateTime();
        $building = new Building();

        $owner->setId(1);
        $owner->setEmail('johngolt@gmail.com');
        $owner->setFirstName('John');
        $owner->setLastName('Golt');
        $owner->setBuilding($building);
        $owner->setPhone('+380936069590');
        $owner->setCreatedAt($date);

        $this->assertNull($owner->getToken());
        $owner->setToken('wewewewewewewew');
        $this->assertNotNull($owner->getToken());
        $this->assertIsString($owner->getToken());

        $this->assertEquals(1, $owner->getId());
        $this->assertEquals('johngolt@gmail.com', $owner->getEmail());
        $this->assertEquals('JOHN', $owner->getFirstName());
        $this->assertEquals('GOLT', $owner->getLastName());
        $this->assertEquals('JOHN GOLT', $owner->getFullName());
        $this->assertEquals('johngolt@gmail.com', $owner->getEmail());
        $this->assertEquals('380936069590', $owner->getPhone());
        $this->assertEquals($building, $owner->getBuilding());
        $this->assertEquals($date, $owner->getCreatedAt());
        $this->assertEquals(false, $owner->isActivated());
        $owner->setIsActivated(true);
        $this->assertEquals(true, $owner->isActivated());

        $this->testSetBuilding();
        $this->testNotifications();
    }

    public function testSetBuilding()
    {
        $owner = new Owner();
        $building = new Building();

        $owner->setBuilding($building);

        $this->assertNotNull($building);
        $this->assertEquals($owner->getBuilding(), $building);
    }

    public function testNotifications()
    {
        $owner = new Owner();
        $this->assertEmpty($owner->getNotifications());

        $notification = new OwnerEmailNotify();
        $owner->addNotification($notification);
        $collection = new ArrayCollection([$notification]);

        $this->assertEquals($collection, $owner->getNotifications());
        $owner->setNotifications([]);
        $this->assertEquals([], $owner->getNotifications());

        $owner->setNotifications([$notification]);
        $this->assertEquals([$notification], $owner->getNotifications());
    }
}