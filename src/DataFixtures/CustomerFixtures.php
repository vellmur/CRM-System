<?php

namespace App\DataFixtures;

use App\Entity\Client\Client;
use App\Entity\Customer\Apartment;
use App\Entity\Customer\Customer;
use App\Manager\MemberManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory;

class CustomerFixtures extends Fixture implements DependentFixtureInterface
{
    private $memberManager;

    const ENABLED_CUSTOMER = [
        'firstname' => 'JOHN',
        'lastname' => 'WICK',
        'email' => 'johnwick@example.com',
        'phone' => '380932332123',
        'apartment' => [
            'number' => 63
        ],
        'notes' => 'We want to track our home. So we are your clients.'
    ];

    /**
     * CustomerFixtures constructor.
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
        $firstCustomer = self::ENABLED_CUSTOMER;

        $client = $this->getReference(UserFixtures::ENABLED_USER_REFERENCE)->getClient();

        $customer = $this->createCustomer(
            $client,
            $firstCustomer['firstname'],
            $firstCustomer['lastname'],
            $firstCustomer['email'],
            $firstCustomer['phone'],
            $firstCustomer['notes'],
            $firstCustomer['apartment']['number']
        );

        $this->memberManager->addCustomer($client, $customer);

        $faker = Factory::create();

        for ($i = 0; $i < 10; $i++) {
            $customer = $this->createCustomer(
                $client,
                $faker->firstName,
                $faker->lastName,
                $faker->email,
                $faker->phoneNumber,
                $faker->text,
                $faker->numberBetween(1, 100)
            );

            $this->memberManager->addCustomer($client, $customer);
        }
    }

    /**
     * @param Client $client
     * @param string $firstname
     * @param string $lastname
     * @param string $email
     * @param bool $phone
     * @param string $notes
     * @param string $apartmentNumber
     * @return Customer
     * @throws \Exception
     */
    private function createCustomer(Client $client, string $firstname, string $lastname, string $email, string $phone, string $notes, string $apartmentNumber)
    {
        $customer = new Customer();
        $customer->setFirstname($firstname);
        $customer->setLastname($lastname);
        $customer->setEmail($email);
        $customer->setPhone($phone);
        $customer->setNotes($notes);

        $apartment = new Apartment();
        $apartment->setBuilding($client);
        $apartment->setNumber($apartmentNumber);

        $customer->setApartment($apartment);

        return $customer;
    }

    public function getDependencies()
    {
        return [
            UserFixtures::class,
        ];
    }
}