<?php

namespace App\Tests;

use \Codeception\Util\Locator;

class LogInCest
{
    /**
     * @param FunctionalTester $I
     */
    public function testGoToLoginPage(FunctionalTester $I)
    {
        $I->wantToTest('Log in page');
        $I->amOnRoute('app_login');
        $I->see('Log into');
    }

    /**
     * @before testGoToLoginPage
     * @param FunctionalTester $I
     */
    public function testLogInWithWrongUsername(FunctionalTester $I)
    {
        $I->wantToTest('Log in with wrong username');

        $I->fillField("#username", 'wrong');
        $I->fillField("#password", 'wrong');

        $I->click('#_submit');
        $I->see('Username or email could not be found.');

        $I->click('#_submit');
        $I->see('Username or email could not be found.');
    }

    /**
     * @before testGoToLoginPage
     * @param FunctionalTester $I
     */
    public function testLogInWithNotConfirmedEmail(FunctionalTester $I)
    {
        $I->wantToTest('Log in with not confirmed email');

        $I->fillField("#username", 'testuser');
        $I->fillField("#password", 'user_passWord');

        $I->click('#_submit');
        $I->see('You need to confirm your email first.');
    }

    /**
     * @before testGoToLoginPage
     * @param FunctionalTester $I
     */
    public function testLogInWithWrongPassword(FunctionalTester $I)
    {
        $I->wantToTest('Log in with wrong password');

        $I->fillField("#username", 'testuser');
        $I->fillField("#password", 'testuser');

        $I->click('#_submit');
        $I->see('Invalid credentials.');
    }

    /**
     * @before testGoToLoginPage
     * @param FunctionalTester $I
     */
    public function testSuccessfulLogIn(FunctionalTester $I)
    {
        $I->wantToTest('Successful Log in');

        $I->fillField("#username", 'testuser');
        $I->fillField("#password", 'testuser');

        $I->click('#_submit');
        $I->canSeeInCurrentUrl('/module/');
        $I->canSeeResponseCodeIs(200);
        $I->see('Black Dirt Software');
    }
}
