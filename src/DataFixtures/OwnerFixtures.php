<?php

namespace App\DataFixtures;

use App\Entity\Building\Building;
use App\Entity\Owner\Apartment;
use App\Entity\Owner\Owner;
use App\Manager\MemberManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory;

class OwnerFixtures extends Fixture implements DependentFixtureInterface
{
    private $memberManager;

    const ENABLED_OWNER = [
        'firstname' => 'JOHN',
        'lastname' => 'WICK',
        'email' => 'johnwick@example.com',
        'phone' => '380932332123',
        'apartment' => [
            'number' => 63
        ],
        'notes' => 'We want to track our home. So we are your buildings.'
    ];

    /**
     * OwnerFixtures constructor.
     * @param MemberManager $memberManager
     */
    public function __construct(MemberManager $memberManager)
    {
        $this->memberManager = $memberManager;
    }

    /**
     * @param ObjectManager $manager
     * @throws \Exception
     */
    public function load(ObjectManager $manager)
    {
        $firstOwner = self::ENABLED_OWNER;

        $building = $this->getReference(UserFixtures::ENABLED_USER_REFERENCE)->getBuilding();

        $owner = $this->createOwner(
            $building,
            $firstOwner['firstname'],
            $firstOwner['lastname'],
            $firstOwner['email'],
            $firstOwner['phone'],
            $firstOwner['notes'],
            $firstOwner['apartment']['number']
        );

        $this->memberManager->addOwner($building, $owner);

        $faker = Factory::create();

        for ($i = 0; $i < 10; $i++) {
            $owner = $this->createOwner(
                $building,
                $faker->firstName,
                $faker->lastName,
                $faker->email,
                $faker->phoneNumber,
                $faker->text,
                $faker->unique()->numberBetween(1, 100)
            );

            $this->memberManager->addOwner($building, $owner);
        }
    }

    /**
     * @param Building $building
     * @param string $firstname
     * @param string $lastname
     * @param string $email
     * @param string $phone
     * @param string $notes
     * @param string $apartmentNumber
     * @return Owner
     * @throws \Exception
     */
    private function createOwner(Building $building, string $firstname, string $lastname, string $email, string $phone, string $notes, string $apartmentNumber)
    {
        $owner = new Owner();
        $owner->setFirstName($firstname);
        $owner->setLastName($lastname);
        $owner->setEmail($email);
        $owner->setPhone($phone);
        $owner->setNotes($notes);

        $apartment = new Apartment();
        $apartment->setBuilding($building);
        $apartment->setNumber($apartmentNumber);

        $owner->setApartment($apartment);

        return $owner;
    }

    public function getDependencies()
    {
        return [
            UserFixtures::class,
        ];
    }
}
