<?php

namespace App\Tests\Entity\User;

use App\Entity\Building\Building;
use App\Entity\User\User;
use App\Service\Localization\LanguageDetector;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    private $languageDetector;

    protected function setUp(): void
    {
        parent::setUp();

        $this->languageDetector = $this->getMockBuilder(LanguageDetector::class)
            ->enableProxyingToOriginalMethods()
            ->setMethods(['getLanguagesList'])
            ->getMock();
    }

    public function testUserCreate()
    {
        $user = new User();
        $date = new \DateTime();
        $building = new Building();
        $dateFormat = $user::DATE_FORMATS[1];

        $locales = $this->languageDetector->getLanguagesList();

        $user->setId(1);
        $user->setEmail('johngolt@gmail.com');
        $user->setUsername('johngolt');
        $user->setRoles([User::ROLE_OWNER]);
        $user->setPassword('23232wdsadafdsr4r42dasdewraerewreq');
        $user->setPlainPassword('23232wdsadafdsr4r42dasdewraerewreq');
        $user->setConfirmationToken('434343434edaerw4r34');
        $user->setLocale(1);
        $user->setBuilding($building);
        $user->setDateFormat($dateFormat);
        $user->setCreatedAt($date);
        $user->setPasswordRequestedAt($date);
        $user->setEnabled(true);
        $user->setIsActive(true);

        $this->assertEquals(1, $user->getId());
        $this->assertEquals('johngolt@gmail.com', $user->getEmail());
        $this->assertEquals('johngolt', $user->getUsername());
        $this->assertEquals([User::ROLE_OWNER, User::ROLE_USER], $user->getRoles());
        $this->assertEquals('23232wdsadafdsr4r42dasdewraerewreq', $user->getPassword());
        $this->assertEquals('23232wdsadafdsr4r42dasdewraerewreq', $user->getPlainPassword());
        $this->assertEquals('434343434edaerw4r34', $user->getConfirmationToken());
        $this->assertSame($locales[1], $locales[$user->getLocale()]);
        $this->assertEquals($building, $user->getBuilding());
        $this->assertEquals(array_flip($user::DATE_FORMATS)[$dateFormat], $user->getDateFormat());
        $this->assertEquals($date, $user->getCreatedAt());
        $this->assertEquals($date, $user->getPasswordRequestedAt());
        $this->assertEquals(true, $user->isEnabled());
        $this->assertEquals(true, $user->getIsActive());
        $this->assertEquals(1, $user->getLocale());
    }

    public function testSetBuilding()
    {
        $user = new User();
        $building = new Building();

        $user->setBuilding($building);

        $this->assertNotNull($building);
        $this->assertEquals($user->getBuilding(), $building);
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

        $dateFormatName = 'dd-MM-yyyy';

        $this->assertEquals(null, $user->getDateFormat());
        $this->assertEquals($dateFormatName, $user->getDateFormatName());
        $this->assertEquals(null, $user->getTwigFormatDate());

        $user->setDateFormat($dateFormatName);
        $this->assertEquals(array_flip($user::DATE_FORMATS)[$dateFormatName], $user->getDateFormat());
        $this->assertEquals($dateFormatName, $user->getDateFormatName());
        $this->assertEquals($user::TWIG_DATE_FORMATS[$user->getDateFormat()], $user->getTwigFormatDate());
    }

    public function testTimezone()
    {
        $building = new Building();
        $defaultTimezone = 'UTC';
        $this->assertEquals($defaultTimezone, $building->getTimeZone());

        $timezone = 'Europe/Paris';
        $building->setTimezone($timezone);
        $this->assertEquals($timezone, $building->getTimeZone());
    }

    public function testEmptyData()
    {
        $user = new User();
        $this->assertEmpty($user->getSalt());
        $this->assertEmpty($user->eraseCredentials());
    }
}