<?php

namespace App\Tests;

class HomeTestCest
{
    /**
     * @param FunctionalTester $I
     */
    public function testHomePage(FunctionalTester $I)
    {
        $I->wantToTest('Website page');

        $I->amOnPage('/');
        $I->see('BLACK DIRT');
    }

    /**
     * @before testHomePage
     * @param FunctionalTester $I
     */
    public function testGoSignUp(FunctionalTester $I)
    {
        $I->wantToTest('Go from website to Sign up page');
        $I->click('a[href="/register"]');
        $I->see('Create account');
    }

    /**
     * @before testGoSignUp
     * @param FunctionalTester $I
     */
    public function testGoToLogin(FunctionalTester $I)
    {
        $I->wantToTest('Go to log in page of my site');
        $I->click('a[href="/login"]');
        $I->see('Log into');
    }

    /**
     * @param FunctionalTester $I
     */
    public function testGoFromLoginToSignUp(FunctionalTester $I)
    {
        $I->wantToTest('Go from Log in to Sign up');
        $I->amOnPage('/login');
        $I->click('a[href="/register"]');
        $I->see('Create account');
    }
}


