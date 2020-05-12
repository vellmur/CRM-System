<?php

namespace App\Tests\Form\Security;

use App\Entity\User\User;
use App\Form\Security\RegistrationType;
use App\Form\Type\LocaleType;
use App\Service\CountryList;
use App\Service\Localization\LanguageDetector;
use EWZ\Bundle\RecaptchaBundle\Form\Type\EWZRecaptchaType;
use EWZ\Bundle\RecaptchaBundle\Locale\LocaleResolver;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RegistrationTypeTest extends TypeTestCase
{
    private $countryList;

    private $requestStack;

    private $router;

    private $languageDetector;

    protected function setUp() : void
    {
        $this->countryList = $this->createMock(CountryList::class);
        $this->languageDetector = $this->getMockBuilder(LanguageDetector::class)
            ->enableProxyingToOriginalMethods()
                ->setMethods(['getLanguagesList'])
                ->getMock();
        $this->requestStack = $this->createMock(RequestStack::class);
        $this->router = $this->createMock(RouterInterface::class);

        parent::setUp();
    }

    protected function getExtensions()
    {
        $type = new RegistrationType($this->countryList);
        $localeType = new LocaleType($this->router, $this->requestStack);

        return [
            new PreloadedExtension([$localeType], []),
            new PreloadedExtension([$type], []),
            new PreloadedExtension([$this->getEwzRecaptchaType()], []),
            new ValidatorExtension($this->getValidatorExtension()),
        ];
    }

    /**
     * @return EWZRecaptchaType
     */
    private function getEwzRecaptchaType()
    {
        $localeResolver = new LocaleResolver('en', false, $this->requestStack);
        $ewzRecaptchaType = new EWZRecaptchaType('key', true, true, $localeResolver, 'www.google.com');

        return $ewzRecaptchaType;
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
     */
    public function testSubmitValidData($data)
    {
        $formUser = new User();
        $form = $this->factory->create(RegistrationType::class, $formUser, [
            'validation_groups' => ['register_validation', 'Default']
        ]);

        $form->submit($data);

        $locales = LanguageDetector::getLanguagesList();

        $user = new User();
        $user->setUsername($data['username']);
        $user->setEmail($data['email']);
        $user->setLocale($data['locale']);
        $user->setPlainPassword($data['plainPassword']['first']);
        $user->setCreatedAt($formUser->getCreatedAt());

        $this->assertTrue($form->isSynchronized());
        $this->assertTrue($form->isSubmitted());
        $this->assertTrue($form->isValid());

        $this->assertEquals($user, $formUser);

        $this->assertSame($data['username'], $user->getUsername());
        $this->assertSame($data['email'], $user->getEmail());
        $this->assertSame($locales[$data['locale']], $locales[$user->getLocale()]);
        $this->assertSame($data['plainPassword']['first'], $user->getPlainPassword());


        $view = $form->createView();
        $children = $view->children;

        foreach (array_keys($data) as $key) {
            $this->assertArrayHasKey($key, $children);
        }

        $this->assertArrayHasKey('recaptcha', $children);
    }

    /**
     * @return array
     */
    public function getUsersValidData()
    {
        return [
            [
                [
                    'username' => 'whereismy',
                    'email' => 'whereismy@mail.ru',
                    'locale' => 1,
                    'client' => [
                        'name' => 'Where is my'
                    ],
                    "plainPassword" => [
                        'first' => '232dssa23',
                        'second' => '232dssa23'
                    ]
                ]
            ],
            [
                [
                    'username' => 'john',
                    'email' => 'golt',
                    'locale' => 2,
                    'client' => [
                        'name' => 'John Golt'
                    ],
                    "plainPassword" => [
                        'first' => '22233123',
                        'second' => '22233123'
                    ]
                ]
            ],
            [
                [
                    'username' => 'john',
                    'email' => 'golt',
                    'locale' => 3,
                    'client' => [
                        'name' => 'John Golt'
                    ],
                    "plainPassword" => [
                        'first' => '22233123',
                        'second' => '22233123'
                    ]
                ]
            ]
        ];
    }
}