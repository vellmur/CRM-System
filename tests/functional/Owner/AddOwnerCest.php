<?php

namespace App\Tests;

use App\DataFixtures\OwnerFixtures;
use App\DataFixtures\UserFixtures;
use App\Entity\Owner\Apartment;
use App\Entity\Owner\Owner;
use App\Service\Localization\PhoneFormatter;
use Codeception\Example;
use Faker\Factory;

class AddOwnerCest
{
    private $phoneFormatter;

    public function _before(FunctionalTester $I)
    {
        $this->phoneFormatter = new PhoneFormatter(UserFixtures::ADMIN['building']['address']['country']);

        $I->auth();
    }

    /**
     * @param FunctionalTester $I
     */
    public function goToOwnerAddPage(FunctionalTester $I)
    {
        $I->wantToTest('Work of owner add page.');
        $I->amOnPage('/module/owners/add');
        $I->seeInCurrentUrl('/module/owners/add');
        $I->see('Lead');
    }

    /**
     * @before goToOwnerAddPage
     * @dataProvider ownerData
     * @param Example $data
     * @param FunctionalTester $I
     */
    public function testSuccessfulAdd(FunctionalTester $I, Example $data)
    {
        $I->wantToTest('Successful add of Owner to database.');

        $formFields = [
            'owner_firstname' => $data['firstname'],
            'owner_lastname' => $data['lastname'],
            'owner_email' => $data['email'],
            'owner_phone' => $data['phone'],
            'owner_apartment_number' => $data['apartment']['number'],
            'owner_notes' => $data['notes']
        ];

        $this->submitOwnerForm($I, $formFields);
    }

    /**
     * @param FunctionalTester $I
     * @param $formFields
     */
    private function submitOwnerForm(FunctionalTester $I, $formFields)
    {
        $I->fillForm($formFields);
        $I->click('#btn-submit');

        $I->seeInCurrentUrl('/edit');
        $I->see('Owner');

        $formFields['owner_phone'] = $this->phoneFormatter->getCleanPhoneNumber($formFields['owner_phone']);

        $I->seeRecordIsAdded(Owner::class, [
            'firstname' => $formFields['owner_firstname'],
            'lastname' => $formFields['owner_lastname'],
            'email' => $formFields['owner_email'],
            'notes' => $formFields['owner_notes']
        ]);

        $I->seeRecordIsAdded(Apartment::class, [
            'number' => $formFields['owner_apartment_number']
        ]);
    }

    /**
     * @before goToOwnerAddPage
     * @param FunctionalTester $I
     */
    public function testAddingOwnerToSameApartment(FunctionalTester $I)
    {
        $I->wantToTest('Adding of owner to existed apartment.');

        $existedOwner = OwnerFixtures::ENABLED_OWNER;

        $faker = Factory::create();

        $formFields = [
            'owner_firstname' => 'Owner',
            'owner_lastname' => 'Wife',
            'owner_email' => 'ownerwife@mail.com',
            'owner_phone' => substr($faker->phoneNumber . '-' . $faker->phoneNumber, 0, 20),
            'owner_apartment_number' => $existedOwner['apartment']['number'],
            'owner_notes' => 'This is owner to existed apartment'
        ];

        $this->submitOwnerForm($I, $formFields);

        $owner = $I->grabEntityFromRepository(Owner::class, [
            'email' => $existedOwner['email']
        ]);

        $I->seeRecordIsAdded(Owner::class, [
            'email' => $formFields['owner_email'],
            'building' => $owner->getBuilding()->getId()
        ]);
    }

    /**
     * @before goToOwnerAddPage
     * @param FunctionalTester $I
     */
    public function testUniqueValidationError(FunctionalTester $I)
    {
        $I->wantToTest('Unique error while add owner with same email or phone to database.');

        $existedOwner = OwnerFixtures::ENABLED_OWNER;

        $formFields = [
            'owner_firstname' => $existedOwner['firstname'],
            'owner_lastname' => $existedOwner['lastname'],
            'owner_email' => $existedOwner['email'],
            'owner_phone' => $existedOwner['phone'],
            'owner_apartment_number' => $existedOwner['apartment']['number'],
            'owner_notes' => 'This is adding of already existed owner.'
        ];

        $I->fillForm($formFields);
        $I->click('#btn-submit');
        $I->dontSeeInCurrentUrl('/edit');
        $I->see('Lead');

        $I->dontSeeInRepository(Owner::class, [
            'firstname' => $formFields['owner_firstname'],
            'lastname' => $formFields['owner_lastname'],
            'email' => $formFields['owner_email'],
            'phone' => $formFields['owner_phone'],
            'notes' => $formFields['owner_notes']
        ]);

        $I->see('This email is already taken.');
    }

    /**
     * @before goToOwnerAddPage
     * @param FunctionalTester $I
     */
    public function checkValidationErrors(FunctionalTester $I)
    {
        $formFields = [
            'owner_firstname' => '',
            'owner_lastname' => '',
            'owner_email' => '',
            'owner_phone' => null,
            'owner_apartment_number' => null
        ];

        $I->fillForm($formFields);
        $I->click('#btn-submit');

        $I->iSeeValidationErrorLabels($formFields);
    }

    /**
     * @return array
     */
    protected function ownerData()
    {
        $faker = Factory::create();
        $owners = [];

        for ($i = 0; $i < 1; $i++) {
            $owners[] = [
                'firstname' => $faker->firstName,
                'lastname' => $faker->lastName,
                'email' => $faker->email,
                'phone' => substr($faker->phoneNumber . '-' . $faker->phoneNumber, 0, 20),
                'apartment' => [
                    'number' => $faker->numberBetween(1, 100)
                ],
                'notes' => $faker->text
            ];
        }

        return $owners;
    }
}