<?php

namespace App\Tests;

use Codeception\Actor;
use Codeception\Lib\Friend;
use Codeception\Scenario;
use Codeception\Step\Condition;
use Codeception\Util\Locator;

/**
 * Inherited Methods
 * @method void wantToTest($text)
 * @method void wantTo($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method Friend haveFriend($name, $actorClass = NULL)
 *
 * @SuppressWarnings(PHPMD)
*/
class FunctionalTester extends Actor
{
    use _generated\FunctionalTesterActions;

    private $url;

    /**
     * FunctionalTester constructor.
     * @param Scenario $scenario
     * @throws \Exception
     */
    public function __construct(Scenario $scenario)
    {
        parent::__construct($scenario);

        // This is critical and very important to set start up url for functional test with Symfony module!!!
        $this->url = $_ENV['HTTP_PROTOCOL'] . '://' . $_ENV['DOMAIN'];
        $this->setStartPage($this->url);
    }

    /**
     * @param string $page
     * @throws \Exception
     */
    private function setStartPage(string $page)
    {
        $this->getScenario()->runStep(new Condition('amOnPage', func_get_args()));
    }

    public function amOnPage(string $page)
    {
        $page = $this->url . $page;

        return $this->getScenario()->runStep(new Condition('amOnPage', func_get_args()));
    }

    public function seeSoftwareName()
    {
        $this->see($this->grabService('kernel')->getContainer()->getParameter('software_name'));
    }

    /**
     * @param $formFields
     */
    public function fillForm($formFields)
    {
        foreach ($formFields as $fieldId => $value) {
            if (is_array($value)) {
                $this->selectOption("#$fieldId", $value[0]);
            } else {
                $this->fillField("#$fieldId", $value);
            }
        }
    }

    /**
     * @param $entity
     * @param $data
     */
    public function seeRecordIsAdded($entity, $data)
    {
        $this->seeInRepository($entity, $data);
        $dbOwner= $this->grabEntityFromRepository($entity, $data);
        $this->assertNotNull($dbOwner);
        $this->assertNotNull($dbOwner->getId());
        $this->assertIsInt($dbOwner->getId());
    }

    /**
     * @param array $formFields
     */
    public function iSeeValidationErrorLabels(array $formFields)
    {
        foreach ($formFields as $fieldId => $value) {
            $this->iSeeLabelError($fieldId, 'This field is a required.');
        }
    }

    /**
     * @param string $fieldId
     * @param string $error
     */
    public function iSeeLabelError(string $fieldId, string $error)
    {
        $this->canSee($error, Locator::find('label', ['for' => $fieldId, 'class' => 'validation-error-label']));
    }
}
