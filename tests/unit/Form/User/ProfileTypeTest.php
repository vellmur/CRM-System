<?php

namespace App\Tests\Form\User;

use App\Entity\Client\Address;
use App\Entity\Client\Client;
use App\Entity\User\User;
use App\Form\Client\ClientType;
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
        $clientType = new ClientType($timezoneSubscriber);

        return [
            new PreloadedExtension([$localeType], []),
            new PreloadedExtension([$clientType], []),
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
        $formClient = new Client();
        $formUser->setClient($formClient);

        $form = $this->factory->create(ProfileType::class, $formUser);
        $form->submit($data);

        $this->assertTrue($form->isSynchronized());
        $this->assertTrue($form->isSubmitted());
        $this->assertTrue($form->isValid());

        $client = new Client();
        $client->setName($data['client']['name']);
        $client->setEmail($data['client']['email']);
        $client->setCurrency($data['client']['currency']);

        $address = new Address();
        $address->setCountry($data['client']['address']['country']);
        $address->setStreet($data['client']['address']['street']);
        $address->setPostalCode($data['client']['address']['postalCode']);
        $address->setRegion($data['client']['address']['region']);
        $address->setCity($data['client']['address']['city']);
        $client->setAddress($address);
        $client->setToken($formClient->getToken());
        $client->setCreatedAt($formClient->getCreatedAt());

        $user = new User();
        $user->setClient($client);
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
                    'client' => [
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
                    'client' => [
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
                    'client' => [
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