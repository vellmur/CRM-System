<?php

namespace App\Tests;

class SearchOwnerCest
{
    public function _before(FunctionalTester $I)
    {
        $I->auth();
    }

    /**
     * @param FunctionalTester $I
     */
    public function goToOwnerSearchPage(FunctionalTester $I)
    {
        $searchFieldId = 'search';

        $I->wantToTest('Searching on owner search page.');
        $I->amOnPage('/module/owners/list');
        $I->seeInCurrentUrl('/module/owners/list');
        $I->see('Search:');
        $I->seeElement('#' . $searchFieldId, ['placeholder' => 'SEARCH BY OWNER, EMAIL, PHONE, APARTMENT NUMBER ...']);
    }
}
