<?php

namespace App\Tests;

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
    public function testAdd(FunctionalTester $I)
    {
        $I->wantToTest('Successful add of customer to database.');

        $I->amOnPage('/module/customers/customer-add');
        $I->seeInCurrentUrl('/module/customers/customer-add');
        $I->see('Lead');

        $formFields = [
            'customer_firstname' => 'JOHN',
            'customer_lastname' => 'WICK',
            'customer_email' => 'johnwick@example.com',
            'customer_phone' => '380936069590',
            'customer_apartment_number' => 63,
            'customer_notes' => 'We want to track our home. So we are your clients.'
        ];

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
     * @param FunctionalTester $I
     */
    public function testUniqueValidationError(FunctionalTester $I)
    {
        $I->wantToTest('Unique error while add customer with same email or phone to database.');

        $I->amOnPage('/module/customers/customer-add');
        $I->seeInCurrentUrl('/module/customers/customer-add');
        $I->see('Lead');

        $formFields = [
            'customer_firstname' => 'JOHN',
            'customer_lastname' => 'WICK',
            'customer_email' => 'johnwick@example.com',
            'customer_phone' => '380936069590',
            'customer_apartment_number' => 63,
            'customer_notes' => 'We want to track our home. So we are your clients.'
        ];

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

        $I->amOnPage('/module/customers/customer-add');
        $I->fillForm($formFields);
        $I->click('#btn-submit');
        $I->dontSeeInCurrentUrl('/edit');
        $I->seeInCurrentUrl('/module/customers/customer-add');
        $I->see('Lead');
        $I->makeHtmlSnapshot();
    }
}