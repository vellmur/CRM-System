<?php

/*
namespace App\Tests;

use Symfony\Component\HttpFoundation\Session\Session;

class SignUpCest
{
    private $company;

    private $email;

    private $username;

    private $password;

    public function _before(AcceptanceTester $I)
    {
        $faker = \Faker\Factory::create();

        $this->company = $faker->company;
        $this->email = $faker->email;
        $this->username = $faker->userName;
        $this->password = $faker->password(8, 20);

        $I->amOnPage('/register');
        $I->maximizeWindow();
        $I->see('Sign up');
    }


    public function testSignUpAsFarmer(AcceptanceTester $I)
    {
        $I->wantToTest('sign up as farmer');

        $I->selectOption('fos_user_registration_form[language]', '1');
        $I->checkOption('#fos_user_registration_form_level_0');
        $I->fillField('fos_user_registration_form[building][name]', $this->company);
        $I->fillField('fos_user_registration_form[email]', $this->email);
        $I->fillField('fos_user_registration_form[username]', $this->username);
        $I->fillField('fos_user_registration_form[plainPassword][first]', $this->password);
        $I->fillField('fos_user_registration_form[plainPassword][second]', $this->password);

        $I->click('Sign up');
        $I->see('Dashboard');

        $I->canSeeInDatabase('user', ['username' => $this->username, 'email' => $this->email]);
        $I->canSeeInDatabase('building', ['name' => $this->company]);
    }


    public function testSignUpAsGardener(AcceptanceTester $I)
    {
        $I->wantToTest('sign up as gardener');

        $I->selectOption('fos_user_registration_form[language]', 'English');
        $I->checkOption('#fos_user_registration_form_level_1');
        $I->fillField('fos_user_registration_form[building][name]', $this->company);
        $I->fillField('fos_user_registration_form[email]', $this->email);
        $I->fillField('fos_user_registration_form[username]', $this->username);
        $I->fillField('fos_user_registration_form[plainPassword][first]', $this->password);
        $I->fillField('fos_user_registration_form[plainPassword][second]', $this->password);

        $I->click('Sign up');
        $I->see('Dashboard');

        $I->canSeeInDatabase('user', ['username' => $this->username, 'email' => $this->email]);
        $I->canSeeInDatabase('building', ['name' => $this->company]);
    }


    public function testSignUpInRussian(AcceptanceTester $I)
    {
        $I->wantToTest('sign up as farmer in Russian');

        $I->selectOption('fos_user_registration_form[language]', 'Русский');
        $I->checkOption('#fos_user_registration_form_level_0');
        $I->fillField('fos_user_registration_form[building][name]', $this->company);
        $I->fillField('fos_user_registration_form[email]', $this->email);
        $I->fillField('fos_user_registration_form[username]', $this->username);
        $I->fillField('fos_user_registration_form[plainPassword][first]', $this->password);
        $I->fillField('fos_user_registration_form[plainPassword][second]', $this->password);

        $I->click('Sign up');
        $I->see('Панель управления');

        $I->canSeeInDatabase('user', ['username' => $this->username, 'email' => $this->email]);
        $I->canSeeInDatabase('building', ['name' => $this->company]);
    }


    public function testSignUpInSpanish(AcceptanceTester $I)
    {
        $I->wantToTest('sign up as gardener in Spanish');

        $I->selectOption('fos_user_registration_form[language]', 'Espanol');
        $I->checkOption('#fos_user_registration_form_level_1');
        $I->fillField('fos_user_registration_form[building][name]', $this->company);
        $I->fillField('fos_user_registration_form[email]', $this->email);
        $I->fillField('fos_user_registration_form[username]', $this->username);
        $I->fillField('fos_user_registration_form[plainPassword][first]', $this->password);
        $I->fillField('fos_user_registration_form[plainPassword][second]', $this->password);

        $I->click('Sign up');
        $I->see('Panel De Control');

        $I->canSeeInDatabase('user', ['username' => $this->username, 'email' => $this->email]);
        $I->canSeeInDatabase('building', ['name' => $this->company]);
    }


    public function testSignUpBackendErrors(AcceptanceTester $I)
    {
        $I->wantToTest('sign up backend form errors');

        $I->click('Sign up');

        $I->see('Please choose your language');
        $I->see('Please choose your level');
        $I->see('Please type your Farm/Garden name');
        $I->see('Please enter an email');
        $I->see('Please enter a username');
        $I->see('Please enter a password');

        $I->selectOption('fos_user_registration_form[language]', 'Espanol');
        $I->click('Sign up');

        $I->dontSee('Please choose your language');
        $I->see('Please choose your level');
        $I->see('Please type your Farm/Garden name');
        $I->see('Please enter an email');
        $I->see('Please enter a username');
        $I->see('Please enter a password');

        $I->checkOption('#fos_user_registration_form_level_1');
        $I->click('Sign up');

        $I->dontSee('Please choose your language');
        $I->dontSee('Please choose your level');
        $I->see('Please type your Farm/Garden name');
        $I->see('Please enter an email');
        $I->see('Please enter a username');
        $I->see('Please enter a password');

        $I->fillField('fos_user_registration_form[building][name]', $this->company);
        $I->click('Sign up');

        $I->dontSee('Please choose your language');
        $I->dontSee('Please choose your level');
        $I->dontSee('Please type your Farm/Garden name');
        $I->see('Please enter an email');
        $I->see('Please enter a username');
        $I->see('Please enter a password');

        $I->fillField('fos_user_registration_form[email]', $this->email);
        $I->click('Sign up');

        $I->dontSee('Please choose your language');
        $I->dontSee('Please choose your level');
        $I->dontSee('Please type your Farm/Garden name');
        $I->dontSee('Please enter an email');
        $I->see('Please enter a username');
        $I->see('Please enter a password');

        $I->fillField('fos_user_registration_form[username]', $this->username);
        $I->click('Sign up');

        $I->dontSee('Please choose your language');
        $I->dontSee('Please choose your level');
        $I->dontSee('Please type your Farm/Garden name');
        $I->dontSee('Please enter an email');
        $I->dontSee('Please enter a username');
        $I->see('Please enter a password');

        $I->fillField('fos_user_registration_form[plainPassword][first]', $this->password);
        $I->click('Sign up');

        $I->dontSee('Please choose your language');
        $I->dontSee('Please choose your level');
        $I->dontSee('Please type your Farm/Garden name');
        $I->dontSee('Please enter an email');
        $I->dontSee('Please enter a username');
        $I->see("The entered passwords don't match");

        $I->fillField('fos_user_registration_form[plainPassword][second]', $this->password);
        $I->click('Sign up');

        $I->dontSee('Please choose your language');
        $I->dontSee('Please choose your level');
        $I->dontSee('Please type your Farm/Garden name');
        $I->dontSee('Please enter an email');
        $I->dontSee('Please enter a username');
        $I->see("The entered passwords don't match");

        $I->fillField('fos_user_registration_form[plainPassword][first]', $this->password);
        $I->fillField('fos_user_registration_form[plainPassword][second]', $this->password);
        $I->fillField('fos_user_registration_form[building][name]', 'La Nay Ferme');
        $I->click('Sign up');

        $I->dontSee('Please choose your language');
        $I->dontSee('Please choose your level');
        $I->dontSee('Please type your Farm/Garden name');
        $I->dontSee('Please enter an email');
        $I->dontSee('Please enter a username');
        $I->dontSee("The entered passwords don't match");
        $I->see('This name is already taken.');

        $I->fillField('fos_user_registration_form[building][name]', $this->company);
        $I->fillField('fos_user_registration_form[email]', 'cf@soft.org');
        $I->click('Sign up');

        $I->dontSee('Please choose your language');
        $I->dontSee('Please choose your level');
        $I->dontSee('Please type your Farm/Garden name');
        $I->dontSee('Please enter an email');
        $I->dontSee('Please enter a username');
        $I->see('The email is already used');

        $I->fillField('fos_user_registration_form[email]', $this->email);
        $I->fillField('fos_user_registration_form[username]', 'test');
        $I->click('Sign up');

        $I->dontSee('Please choose your language');
        $I->dontSee('Please choose your level');
        $I->dontSee('Please type your Farm/Garden name');
        $I->dontSee('Please enter an email');
        $I->dontSee('Please enter a username');
        $I->dontSee('The email is already used');
        $I->see('The username is already used');

        $I->fillField('fos_user_registration_form[plainPassword][first]', $this->password);
        $I->fillField('fos_user_registration_form[plainPassword][second]', $this->password);
        $I->fillField('fos_user_registration_form[username]', $this->username);
        $I->click('Sign up');

        $I->see('Dashboard');

        $I->canSeeInDatabase('user', ['username' => $this->username, 'email' => $this->email]);
        $I->canSeeInDatabase('building', ['name' => $this->company]);
    }
}*/
