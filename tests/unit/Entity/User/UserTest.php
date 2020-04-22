<?php

namespace App\Tests\Entity\User;

use App\Entity\Client\Client;
use App\Entity\Client\Team;
use App\Entity\Translation\TranslationLocale;
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
        $locale = new TranslationLocale();
        $locale->setCode('en');
        $client = new Client();
        $team = new Team($client, $user);
        $dateFormat = $user::DATE_FORMATS[1];

        $user->setId(1);
        $user->setEmail('johngolt@gmail.com');
        $user->setUsername('johngolt');
        $user->setRoles(['ROLE_OWNER']);
        $user->setPassword('23232wdsadafdsr4r42dasdewraerewreq');
        $user->setPlainPassword('23232wdsadafdsr4r42dasdewraerewreq');
        $user->setConfirmationToken('434343434edaerw4r34');
        $user->setLocale($locale);
        $user->setTeam($team);
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
        $this->assertEquals($locale, $user->getLocale());
        $this->assertEquals($team, $user->getTeam());
        $this->assertEquals($client, $user->getClient());
        $this->assertEquals(array_flip($user::DATE_FORMATS)[$dateFormat], $user->getDateFormat());
        $this->assertEquals($date, $user->getCreatedAt());
        $this->assertEquals($date, $user->getPasswordRequestedAt());
        $this->assertEquals(true, $user->isEnabled());
        $this->assertEquals(true, $user->getIsActive());
    }

    public function testSetTeam()
    {
        $user = new User();
        $client = new Client();
        $team = new Team($client, $user);

        $user->setTeam($team);
        $userTeam = $user->getTeam();

        $this->assertNotNull($userTeam);
        $this->assertEquals($userTeam->getUser(), $user);
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
        $user = new User();
        $client = new Client();

        $defaultTimezone = 'UTC';
        $this->assertEquals(null, $user->getTimeZone());
        $this->assertEquals($defaultTimezone, $client->getTimeZone());

        $team = new Team($client, $user);
        $user->setTeam($team);

        $this->assertEquals($defaultTimezone, $user->getTimeZone());

        $timezone = 'Europe/Paris';
        $client->setTimezone($timezone);

        $this->assertEquals($timezone, $client->getTimeZone());
        $this->assertEquals($timezone, $user->getTimeZone());
    }

    public function testEmptyData()
    {
        $user = new User();
        $this->assertEmpty($user->getSalt());
        $this->assertEmpty($user->eraseCredentials());
    }
}