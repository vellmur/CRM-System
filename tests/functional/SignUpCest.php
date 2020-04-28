<?php

namespace App\Tests;

use App\DataFixtures\UserFixtures;
use \Codeception\Util\Locator;

class SignUpCest
{
    /**
     * @param FunctionalTester $I
     */
    public function testGoToSignUpPage(FunctionalTester $I)
    {
        $I->wantToTest('Sign up page');
        $I->amOnRoute('app_registration');
        $I->see('Create account');
    }

    /**
     * @before testGoToSignUpPage
     * @param FunctionalTester $I
     */
    public function testSignUpWithEmptyFields(FunctionalTester $I)
    {
        $I->wantToTest('Sign up with empty fields');

        $formFields = [
            'registration_username' => '',
            'registration_locale' => null,
            'registration_email' => '',
            'registration_client_name' => '',
            'registration_plainPassword_first' => '',
            'registration_plainPassword_second' => ''
        ];

        foreach ($formFields as $fieldId => $value) {
            if ($value !== null) {
                $I->fillField("#$fieldId", $value);
            }
        }

        $I->click('#_submit');
        $I->see('Create account');

       foreach ($formFields as $fieldId => $value) {
            $this->iSeeLabelError($I, $fieldId, 'This field is a required.');
        }
    }

    /**
     * @before testGoToSignUpPage
     * @param FunctionalTester $I
    */
    public function testSignUpWithUniqueError(FunctionalTester $I)
    {
        $I->wantToTest('Sign up with unique validation');

        $enabledUser = UserFixtures::ENABLED_USER;

       // die(var_dump($enabledUser['client']['name']));

        $formFields = [
            'registration_username' => $enabledUser['username'],
            'registration_email' => $enabledUser['email'],
            'registration_locale' => 0,
            'registration_client_name' => $enabledUser['client']['name'],
            'registration_plainPassword_first' => 'testuser',
            'registration_plainPassword_second' => 'testuser'
        ];

        $this->fillForm($I, $formFields);
        $I->click('#_submit');
        $I->see('Create account');

        $this->iSeeLabelError($I, 'registration_username', 'This value must be unique.');
        $this->iSeeLabelError($I, 'registration_email', 'This value must be unique.');
        $this->iSeeLabelError($I, 'registration_client_name', 'This value must be unique.');
    }

    /**
     * @before testGoToSignUpPage
     * @param FunctionalTester $I
     */
    public function testSuccessfulSignUp(FunctionalTester $I)
    {
        $I->wantToTest('Successful Sign Up');

        $formFields = [
            'registration_username' => 'johngolt',
            'registration_locale' => 0,
            'registration_email' => 'johngolt@example.com',
            'registration_client_name' => 'John Golt',
            'registration_plainPassword_first' => 'johngolt',
            'registration_plainPassword_second' => 'johngolt'
        ];

        $this->fillForm($I, $formFields);
        $I->click('#_submit');

        $I->canSeeInCurrentUrl('/register/check-email');
        $I->see('Your account has been created');
        $I->seeSoftwareName();
    }

    /**
     * @param FunctionalTester $I
     * @param string $fieldId
     * @param string $error
     */
    private function iSeeLabelError(FunctionalTester $I, string $fieldId, string $error)
    {
        $I->canSee($error, Locator::find('label', ['for' => $fieldId]));
    }

    /**
     * @param FunctionalTester $I
     * @param $formFields
     */
    private function fillForm(FunctionalTester $I, $formFields)
    {
        foreach ($formFields as $fieldId => $value) {
            if (is_int($value)) {
                $I->selectOption("#$fieldId", $value);
            } else {
                $I->fillField("#$fieldId", $value);
            }
        }
    }
}
