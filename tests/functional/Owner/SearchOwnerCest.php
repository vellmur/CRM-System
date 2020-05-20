<?php

namespace App\Tests;

use App\Entity\Owner\Owner;
use Codeception\Util\Locator;

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
        $searchFormName = '#owner_search';
        $searchFieldId = 'search_text';
        $searchListId = '#content_list';

        $I->wantToTest('Searching on owner search page.');
        $I->amOnPage('/module/owners/search');
        $I->seeInCurrentUrl('/module/owners/search');
        $I->see('Search:');
        $I->seeElement('#' . $searchFieldId, ['placeholder' => 'SEARCH BY OWNER, EMAIL, PHONE, APARTMENT NUMBER ...']);

        $owners = $I->grabEntitiesFromRepository(Owner::class);

        $searchField = Locator::find('input', ['id' => $searchFieldId]);
        $I->seeElement($searchField);

        $list ='div' . $searchListId . '>table>tbody';

        foreach ($owners as $owner)
        {
            $I->fillField($searchField, $owner->getFullname());
            $I->submitForm($searchFormName, [
                'search' => $owner->getFullname()
            ]);

            $resultName = Locator::elementAt($list . '>tr>td', 1);
            $I->assertEquals($owner->getFullname(), $I->grabTextFrom($resultName));
            $I->assertEquals(1, $I->grabTextFrom('#content_counter'));
        }
    }
}
