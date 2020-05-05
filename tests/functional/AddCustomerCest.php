<?php

namespace App\Tests;

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
        $I->amOnPage('/module/customers/customer-add');
        $I->seeInCurrentUrl('/module/customers/customer-add');
        $I->see('Lead');

        $formFields = [
            'customer_firstname' => 'John',
            'customer_lastname' => 'Wick',
            'customer_email' => 'johnwick@example.com',
            'customer_phone' => '+380 93 606 9590',
            'customer_apartment_number' => '63',
            'customer_notes' => 'We want to track our home. So we are your clients.'
        ];

        $I->fillForm($formFields);
        $I->click('#btn-submit');

        $I->seeInCurrentUrl('/edit');
        $I->see('Customer');
    }
}