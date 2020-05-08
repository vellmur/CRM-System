<?php

namespace App\Tests;

use App\Entity\Customer\Customer;
use Codeception\Util\Locator;

class SearchCustomerCest
{
    public function _before(FunctionalTester $I)
    {
        $I->auth();
    }

    /**
     * @param FunctionalTester $I
     */
    public function goToCustomerSearchPage(FunctionalTester $I)
    {
        $searchFormName = '#customer_search';
        $searchFieldId = 'search_text';
        $searchListId = '#content_list';

        $I->wantToTest('Searching on customer search page.');
        $I->amOnPage('/module/customers/search');
        $I->seeInCurrentUrl('/module/customers/search');
        $I->see('Search:');
        $I->seeElement('#' . $searchFieldId, ['placeholder' => 'SEARCH BY CUSTOMER, EMAIL...']);

        $customers = $I->grabEntitiesFromRepository(Customer::class);

        $searchField = Locator::find('input', ['id' => $searchFieldId]);
        $I->seeElement($searchField);

        $list ='div' . $searchListId . '>table>tbody';

        foreach ($customers as $customer)
        {
            $I->fillField($searchField, $customer->getFullname());
            $I->submitForm($searchFormName, [
                'search' => $customer->getFullname()
            ]);

            $resultName = Locator::elementAt($list . '>tr>td', 1);
            $I->assertEquals($customer->getFullname(), $I->grabTextFrom($resultName));
            $I->assertEquals(1, $I->grabTextFrom('#content_counter'));
        }
    }
}
