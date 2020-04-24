<?php

namespace App\Tests;

class HomeCest
{
    public function testHomePage(AcceptanceTester $I)
    {
        $I->wantToTest('Load home page of my site');

        $I->amOnPage('/');
        $I->see('Black Dirt is a software platform.');
    }
}