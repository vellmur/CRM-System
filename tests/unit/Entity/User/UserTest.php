<?php

namespace App\Tests\Entity\User;

use App\Entity\Client\Client;
use App\Entity\User\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function testUserCreate()
    {
        $user = new User();
        $date = new \DateTime();
        $client = new Client();
        $dateFormat = $user::DATE_FORMATS[1];

        $locales = $user::LOCALES;

        $user->setId(1);
        $user->setEmail('johngolt@gmail.com');
        $user->setUsername('johngolt');
        $user->setRoles(['ROLE_OWNER']);
        $user->setPassword('23232wdsadafdsr4r42dasdewraerewreq');
        $user->setPlainPassword('23232wdsadafdsr4r42dasdewraerewreq');
        $user->setConfirmationToken('434343434edaerw4r34');
        $user->setLocale(1);
        $user->setClient($client);
        $user->setDateFormat($dateFormat);
        $user->setCreatedAt($date);
        $user->setPasswordRequestedAt($date);
        $user->setEnabled(true);
        $user->setIsActive(true);

        $this->assertEquals(1, $user->getId());
        $this->assertEquals('johngolt@gmail.com', $user->getEmail());
        $this->assertEquals('johngolt', $user->getUsername());
        $this->assertEquals(['ROLE_OWNER', 'ROLE_USER'], $user->getRoles());
        $this->assertEquals('23232wdsadafdsr4r42dasdewraerewreq', $user->getPassword());
        $this->assertEquals('23232wdsadafdsr4r42dasdewraerewreq', $user->getPlainPassword());
        $this->assertEquals('434343434edaerw4r34', $user->getConfirmationToken());
        $this->assertSame($locales[1], $locales[$user->getLocale()]);
        $this->assertEquals($client, $user->getClient());
        $this->assertEquals(array_flip($user::DATE_FORMATS)[$dateFormat], $user->getDateFormat());
        $this->assertEquals($date, $user->getCreatedAt());
        $this->assertEquals($date, $user->getPasswordRequestedAt());
        $this->assertEquals(true, $user->isEnabled());
        $this->assertEquals(true, $user->getIsActive());
    }

    public function testSetClient()
    {
        $user = new User();
        $client = new Client();

        $user->setClient($client);

        $this->assertNotNull($client);
        $this->assertEquals($user->getClient(), $client);
    }

    public function testIsPasswordRequestNonExpired()
    {
        $date = new \DateTime();
        $user = new User();
        $user->setPasswordRequestedAt($date);
        $this->assertTrue($user->isPasswordRequestNonExpired(7200));
    }

    public function testIsPasswordRequestExpired()
    {
        $date = new \DateTime();
        $date->modify('-3 hours');
        $user = new User();
        $user->setPasswordRequestedAt($date);
        $this->assertFalse($user->isPasswordRequestNonExpired(7200));
    }

    public function testDateFormat()
    {
        $user = new User();
        $user->setDateFormat('Y-m-d');

        $this->assertEquals(null, $user->getDateFormat());
        $this->assertEquals(null, $user->getDateFormatName());
        $this->assertEquals(null, $user->getTwigFormatDate());

        $dateFormatName = 'dd-MM-yyyy';
        $user->setDateFormat($dateFormatName);
        $this->assertEquals(array_flip($user::DATE_FORMATS)[$dateFormatName], $user->getDateFormat());
        $this->assertEquals($dateFormatName, $user->getDateFormatName());
        $this->assertEquals($user::TWIG_DATE_FORMATS[$user->getDateFormat()], $user->getTwigFormatDate());
    }

    public function testTimezone()
    {
        $client = new Client();
        $defaultTimezone = 'UTC';
        $this->assertEquals($defaultTimezone, $client->getTimeZone());

        $timezone = 'Europe/Paris';
        $client->setTimezone($timezone);
        $this->assertEquals($timezone, $client->getTimeZone());
    }

    public function testEmptyData()
    {
        $user = new User();
        $this->assertEmpty($user->getSalt());
        $this->assertEmpty($user->eraseCredentials());
    }
}