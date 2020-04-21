<?php namespace App\Tests;
use App\Tests\AcceptanceTester;

class FirstCest
{
    public function _before(AcceptanceTester $I)
    {
    }

    public function frontpageWorks(AcceptanceTester $I)
    {
        $I->amOnPage('/');
        $I->see('Home');
    }

    // tests
    public function tryToTest(AcceptanceTester $I)
    {
    }
}
