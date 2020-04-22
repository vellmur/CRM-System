<?php

namespace App\Tests;

class HomeTestCest
{
    function _before(FunctionalTester $I)
    {
        $I->amOnPage('http://customer.local/');
    }

    public function testHomePage(FunctionalTester $I)
    {
        $I->wantToTest('Visit home page of software');

        $I->auth();
        $I->amOnRoute('customer_list');
        $I->seeElement('#summary-search-path');

        $I->seeResponseCodeIs(200);
        $I->see('Black Dirt');

        $I->amOnPage('/');
    }

    /*
    public function testGoSignUp(FunctionalTester $I)
    {
        $I->wantToTest('Load sign up page of my site');
        $I->amOnPage('/');
        $I->see('Black Dirt');
        $I->click('a[href="/register"]');
        $I->see('Create account');
    }

    public function testGoToLogin(FunctionalTester $I)
    {
        $I->wantToTest('Load log in page of my site');
        $I->amOnPage('/register');
        $I->see('Create account');
        $I->click('a[href="/login"]');
        $I->see('Log into');
    }

    public function testGoFromLoginToSignUp(FunctionalTester $I)
    {
        $I->wantToTest('Load log in page of my site');
        $I->amOnPage('/login');
        $I->click('a[href="/register"]');
        $I->see('Create account');
    }*/
}


