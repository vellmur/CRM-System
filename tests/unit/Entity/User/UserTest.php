<?php

namespace App\Tests\Unit\Entity\User;

use App\Entity\Client\Client;
use App\Entity\Client\Team;
use App\Entity\Translation\TranslationLocale;
use App\Entity\User\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    protected $user;

    protected function setUp(): void
    {
        $this->user = new User();
        parent::setUp();

    }

    /**
     * @throws \Exception
     */
    public function testUserCreate()
    {
        $date = new \DateTime();
        $locale = new TranslationLocale();
        $locale->setCode('en');
        $client = new Client();
        $team = new Team($client, $this->user);

        $this->user->setId(1);
        $this->user->setEmail('johngolt@gmail.com');
        $this->user->setUsername('johngolt');
        $this->user->setRoles(['ROLE_OWNER']);
        $this->user->setPassword('23232wdsadafdsr4r42dasdewraerewreq');
        $this->user->setPlainPassword('23232wdsadafdsr4r42dasdewraerewreq');
        $this->user->setConfirmationToken('434343434edaerw4r34');
        $this->user->setLocale($locale);
        $this->user->setTeam($team);
        $this->user->setDateFormat('Y-m-d');
        $this->user->setCreatedAt($date);
        $this->user->setPasswordRequestedAt($date);
        $this->user->setEnabled(true);
        $this->user->setIsActive(true);

        $this->assertEquals(1, $this->user->getId());
        $this->assertEquals('johngolt@gmail.com', $this->user->getEmail());
        $this->assertEquals('johngolt', $this->user->getUsername());
        $this->assertEquals(['ROLE_OWNER', 'ROLE_USER'], $this->user->getRoles());
        $this->assertEquals('23232wdsadafdsr4r42dasdewraerewreq', $this->user->getPassword());
        $this->assertEquals('23232wdsadafdsr4r42dasdewraerewreq', $this->user->getPlainPassword());
        $this->assertEquals('434343434edaerw4r34', $this->user->getConfirmationToken());
        $this->assertEquals($locale, $this->user->getLocale());
        $this->assertEquals($team, $this->user->getTeam());
        $this->assertEquals($client, $this->user->getClient());
        $this->assertEquals('Y-m-d', $this->user->getDateFormat());
        $this->assertEquals($date, $this->user->getCreatedAt());
        $this->assertEquals($date, $this->user->getPasswordRequestedAt());
        $this->assertEquals(true, $this->user->isEnabled());
        $this->assertEquals(true, $this->user->getIsActive());
    }
}