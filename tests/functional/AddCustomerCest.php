<?php

namespace App\Tests;

use App\DataFixtures\CustomerFixtures;
use App\Entity\Customer\Apartment;
use App\Entity\Customer\Customer;

class AddCustomerCest
{
    public function _before(FunctionalTester $I)
    {
        $I->auth();
    }

    /**
     * @param FunctionalTester $I
     */
    public function goToCustomerAddPage(FunctionalTester $I)
    {
        $I->amOnPage('/module/customers/customer-add');
        $I->seeInCurrentUrl('/module/customers/customer-add');
        $I->see('Lead');
    }

    /**
     * @before goToCustomerAddPage
     * @param FunctionalTester $I
     */
    public function testAdd(FunctionalTester $I)
    {
        $I->wantToTest('Successful add of customer to database.');

        $formFields = [
            'customer_firstname' => 'JACK',
            'customer_lastname' => 'JONES',
            'customer_email' => 'jackjones@gmail.com',
            'customer_phone' => '380432037231',
            'customer_apartment_number' => 62,
            'customer_notes' => 'Hello world'
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
    public function testAddCustomerToSameApartment(FunctionalTester $I)
    {
        $I->wantToTest('Adding of customer to existed apartment.');

        $existedCustomer = CustomerFixtures::ENABLED_CUSTOMER;

        $formFields = [
            'customer_firstname' => 'Customer',
            'customer_lastname' => 'Wife',
            'customer_email' => 'customerwife@mail.com',
            'customer_phone' => '384378927568',
            'customer_apartment_number' => $existedCustomer['apartment']['number'],
            'customer_notes' => 'This is customer to existed apartment'
        ];

        $this->submitCustomerForm($I, $formFields);

        $customer = $I->grabEntityFromRepository(Customer::class, [
            'email' => $existedCustomer['email']
        ]);

        $I->seeRecordIsAdded(Customer::class, [
            'email' => $formFields['customer_email'],
            'client' => $customer->getClient()->getId()
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
            'customer_phone' => '',
            'customer_apartment_number' => null
        ];

        $I->fillForm($formFields);
        $I->click('#btn-submit');

        $I->iSeeValidationErrorLabels($formFields);
    }
}