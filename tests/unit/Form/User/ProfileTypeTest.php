<?php

namespace App\Tests\Form\User;

use App\Entity\Building\Address;
use App\Entity\Building\Building;
use App\Entity\User\User;
use App\Form\Building\BuildingType;
use App\Form\Subscriber\ProfileSubscriber;
use App\Form\Subscriber\TimezoneSubscriber;
use App\Form\Type\CurrencyType;
use App\Form\Type\LocaleType;
use App\Form\User\ProfileType;
use App\Service\Localization\CurrencyFormatter;
use App\Service\Localization\LanguageDetector;
use App\Service\LocationService;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ProfileTypeTest extends TypeTestCase
{
    private $sessionInterface;

    private $router;

    private $requestStack;

    private $languageDetector;

    private $formFactory;

    private $timezoneSubscriber;

    private $locationService;

    private $currencyFormatter;

    protected function setUp() : void
    {
        $this->languageDetector = $this->getMockBuilder(LanguageDetector::class)
            ->enableProxyingToOriginalMethods()
            ->setMethods(['getLanguagesList'])
            ->getMock();
        $this->sessionInterface = $this->createMock(SessionInterface::class);
        $this->router = $this->createMock(RouterInterface::class);
        $this->requestStack = $this->createMock(RequestStack::class);
        $this->formFactory = $this->createMock(FormFactoryInterface::class);
        $this->timezoneSubscriber = $this->createMock(TimezoneSubscriber::class);
        $this->locationService = $this->createMock(LocationService::class);
        $this->currencyFormatter = $this->createMock(CurrencyFormatter::class);

        parent::setUp();
    }

    /**
     * @return array
     */
    protected function getExtensions()
    {
        $profileSubscriber = new ProfileSubscriber($this->sessionInterface, $this->languageDetector);
        $type = new ProfileType($profileSubscriber);
        $localeType = new LocaleType($this->router, $this->requestStack);

        $currencyType = new CurrencyType($this->currencyFormatter);

        $timezoneSubscriber = new TimezoneSubscriber($this->formFactory, $this->locationService);
        $buildingType = new BuildingType($timezoneSubscriber);

        return [
            new PreloadedExtension([$localeType], []),
            new PreloadedExtension([$buildingType], []),
            new PreloadedExtension([$currencyType], []),
            new PreloadedExtension([$type], []),
            new ValidatorExtension($this->getValidatorExtension()),
        ];
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|ValidatorInterface
     */
    private function getValidatorExtension()
    {
        $validator = $this->createMock(ValidatorInterface::class);
        $validator
            ->method('validate')
            ->will($this->returnValue(new ConstraintViolationList()));
        $validator
            ->method('getMetadataFor')
            ->will($this->returnValue(new ClassMetadata(Form::class)));

        return $validator;
    }

    /**
     * @dataProvider getUsersValidData
     * @param $data
     * @throws \Exception
     */
    public function testSubmitValidData($data)
    {
        $formUser = new User();
        $formBuilding = new Building();
        $formUser->setBuilding($formBuilding);

        $form = $this->factory->create(ProfileType::class, $formUser);
        $form->submit($data);

        $this->assertTrue($form->isSynchronized());
        $this->assertTrue($form->isSubmitted());
        $this->assertTrue($form->isValid());

        $building = new Building();
        $building->setName($data['building']['name']);
        $building->setEmail($data['building']['email']);
        $building->setCurrency($data['building']['currency']);

        $address = new Address();
        $address->setCountry($data['building']['address']['country']);
        $address->setStreet($data['building']['address']['street']);
        $address->setPostalCode($data['building']['address']['postalCode']);
        $address->setRegion($data['building']['address']['region']);
        $address->setCity($data['building']['address']['city']);
        $building->setAddress($address);
        $building->setToken($formBuilding->getToken());
        $building->setCreatedAt($formBuilding->getCreatedAt());

        $user = new User();
        $user->setBuilding($building);
        $user->setLocale($data['locale']);
        $user->setCreatedAt($formUser->getCreatedAt());
        $user->setConfirmationToken($formUser->getConfirmationToken());

        $this->assertEquals($user, $formUser);
    }

    /**
     * @return array
     */
    public function getUsersValidData()
    {
        return [
            [
                [
                    'building' => [
                        'name' => 'Where is my mind',
                        'email' => 'whereismymind@mail.com',
                        'currency' => '&#8381;',
                        'address' => [
                            'country' => 'ru',
                            'street' => 'Улица лучших людей',
                            'postalCode' => '131001',
                            'region' => 'Краснодарская область',
                            'city' => 'Краснодар',
                        ]
                    ],
                    'locale' => 1
                ]
            ],
            [
                [
                    'building' => [
                        'name' => 'John Golt',
                        'email' => 'johngolt@mail.com',
                        'currency' => '&#8372;',
                        'address' => [
                            'country' => 'ua',
                            'street' => 'Вулиця кращих людей',
                            'postalCode' => '18001',
                            'region' => 'Київська',
                            'city' => 'Київ'
                        ]
                    ],
                    'locale' => 2
                ]
            ],
            [
                [
                    'building' => [
                        'name' => 'Jack Jones',
                        'email' => 'jackjones@mail.com',
                        'currency' => '&#8381;',
                        'address' => [
                            'country' => 'ru',
                            'street' => 'Street of best people',
                            'postalCode' => '21134',
                            'region' => 'Oklahoma',
                            'city' => 'Ogayo'
                        ]
                    ],
                    'locale' => 2
                ]
            ]
        ];
    }
}