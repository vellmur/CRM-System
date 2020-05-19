<?php

namespace App\Tests;

use App\DataFixtures\CustomerFixtures;
use App\Entity\Customer\Apartment;
use App\Entity\Customer\Customer;
use App\Service\Localization\PhoneFormat;
use App\Service\Localization\PhoneFormatter;
use Codeception\Example;
use Faker\Factory;

class AddCustomerCest
{
    private $countryCode;
    private $phoneFormat;

    public function _before(FunctionalTester $I)
    {
        $this->countryCode = $I->grabEntitiesFromRepository(Customer::class)[0]->getCountry();
        $this->phoneFormat = new PhoneFormat($this->countryCode);

        $I->auth();
    }

    /**
     * @param FunctionalTester $I
     */
    public function goToCustomerAddPage(FunctionalTester $I)
    {
        $I->wantToTest('Work of customer add page.');
        $I->amOnPage('/module/customers/add');
        $I->seeInCurrentUrl('/module/customers/add');
        $I->see('Lead');
    }

    /**
     * @before goToCustomerAddPage
     * @dataProvider customerData
     * @param Example $data
     * @param FunctionalTester $I
     */
    public function testSuccessfulAdd(FunctionalTester $I, Example $data)
    {
        $I->wantToTest('Successful add of customer to database.');

        $formFields = [
            'customer_firstname' => $data['firstname'],
            'customer_lastname' => $data['lastname'],
            'customer_email' => $data['email'],
            'customer_phone' => $data['phone'],
            'customer_apartment_number' => $data['apartment']['number'],
            'customer_notes' => $data['notes']
        ];

        $this->submitCustomerForm($I, $formFields);
    }

    /**
     * @param FunctionalTester $I
     * @param $formFields
     */
    private function submitCustomerForm(FunctionalTester $I, $formFields)
    {
        $I->fillForm($formFields);
        $I->click('#btn-submit');

        $I->seeInCurrentUrl('/edit');
        $I->see('Customer');

        $formatter = new PhoneFormatter($this->phoneFormat, $formFields['customer_phone']);
        $formFields['customer_phone'] = $formatter->getClearPhoneNumber();

        $I->seeRecordIsAdded(Customer::class, [
            'firstname' => $formFields['customer_firstname'],
            'lastname' => $formFields['customer_lastname'],
            'email' => $formFields['customer_email'],
            'phone' => $formFields['customer_phone'],
            'notes' => $formFields['customer_notes']
        ]);

        $I->seeRecordIsAdded(Apartment::class, [
            'number' => $formFields['customer_apartment_number']
        ]);
    }

    /**
     * @before goToCustomerAddPage
     * @param FunctionalTester $I
     */
    public function testAddingCustomerToSameApartment(FunctionalTester $I)
    {
        $I->wantToTest('Adding of customer to existed apartment.');

        $existedCustomer = CustomerFixtures::ENABLED_CUSTOMER;

        $faker = Factory::create();

        $formFields = [
            'customer_firstname' => 'Customer',
            'customer_lastname' => 'Wife',
            'customer_email' => 'customerwife@mail.com',
            'customer_phone' => $faker->phoneNumber . '-' . $faker->phoneNumber,
            'customer_apartment_number' => $existedCustomer['apartment']['number'],
            'customer_notes' => 'This is customer to existed apartment'
        ];

        $this->submitCustomerForm($I, $formFields);

        $customer = $I->grabEntityFromRepository(Customer::class, [
            'email' => $existedCustomer['email']
        ]);

        $I->seeRecordIsAdded(Customer::class, [
            'email' => $formFields['customer_email'],
            'building' => $customer->getBuilding()->getId()
        ]);
    }

    /**
     * @before goToCustomerAddPage
     * @param FunctionalTester $I
     */
    public function testUniqueValidationError(FunctionalTester $I)
    {
        $I->wantToTest('Unique error while add customer with same email or phone to database.');

        $existedCustomer = CustomerFixtures::ENABLED_CUSTOMER;

        $formFields = [
            'customer_firstname' => $existedCustomer['firstname'],
            'customer_lastname' => $existedCustomer['lastname'],
            'customer_email' => $existedCustomer['email'],
            'customer_phone' => $existedCustomer['phone'],
            'customer_apartment_number' => $existedCustomer['apartment']['number'],
            'customer_notes' => 'This is adding of already existed customer.'
        ];

        $I->fillForm($formFields);
        $I->click('#btn-submit');
        $I->dontSeeInCurrentUrl('/edit');
        $I->see('Lead');

        $I->dontSeeInRepository(Customer::class, [
            'firstname' => $formFields['customer_firstname'],
            'lastname' => $formFields['customer_lastname'],
            'email' => $formFields['customer_email'],
            'phone' => $formFields['customer_phone'],
            'notes' => $formFields['customer_notes']
        ]);

        $I->see('This email is already taken.');
    }

    /**
     * @before goToCustomerAddPage
     * @param FunctionalTester $I
     */
    public function checkValidationErrors(FunctionalTester $I)
    {
        $formFields = [
            'customer_firstname' => '',
            'customer_lastname' => '',
            'customer_email' => '',
            'customer_phone' => null,
            'customer_apartment_number' => null
        ];

        $I->fillForm($formFields);
        $I->click('#btn-submit');

        $I->iSeeValidationErrorLabels($formFields);
    }

    /**
     * @return array
     */
    protected function customerData()
    {
        $faker = Factory::create();
        $customers = [];

        for ($i = 0; $i < 10; $i++) {
            $customers[] = [
                'firstname' => $faker->firstName,
                'lastname' => $faker->lastName,
                'email' => $faker->email,
                'phone' => $faker->phoneNumber . '-' . $faker->phoneNumber,
                'apartment' => [
                    'number' => $faker->numberBetween(1, 100)
                ],
                'notes' => $faker->text
            ];
        }

        return $customers;
    }
}