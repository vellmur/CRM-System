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

    /**
     * @throws \Exception
     */
    public function testUserCreate()
    {
        $user = new User();
        $date = new \DateTime();
        $locale = new TranslationLocale();
        $locale->setCode('en');
        $client = new Client();
        $team = new Team($client, $user);

        $user->setId(1);
        $user->setEmail('johngolt@gmail.com');
        $user->setUsername('johngolt');
        $user->setRoles(['ROLE_OWNER']);
        $user->setPassword('23232wdsadafdsr4r42dasdewraerewreq');
        $user->setPlainPassword('23232wdsadafdsr4r42dasdewraerewreq');
        $user->setConfirmationToken('434343434edaerw4r34');
        $user->setLocale($locale);
        $user->setTeam($team);
        $user->setDateFormat('Y-m-d');
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
        $this->assertEquals('Y-m-d', $user->getDateFormat());
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
}