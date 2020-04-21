<?php

namespace App\Tests\Unit\Form\Security;

use App\Entity\User\User;
use App\Form\Security\RegistrationType;
use EWZ\Bundle\RecaptchaBundle\Form\Type\EWZRecaptchaType;
use EWZ\Bundle\RecaptchaBundle\Locale\LocaleResolver;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RegistrationTypeTest extends TypeTestCase
{
    protected function setUp() : void
    {
        parent::setUp();
    }

    protected function getExtensions()
    {
        return [
            new PreloadedExtension([$this->getEwzRecaptchaType()], []),
            new ValidatorExtension($this->getValidatorExtension()),
        ];
    }

    /**
     * @return EWZRecaptchaType
     */
    private function getEwzRecaptchaType()
    {
        $requestStack = $this->createMock(RequestStack::class);
        $localeResolver = new LocaleResolver('en', false, $requestStack);
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
        $user = new User();
        $form = $this->factory->create(RegistrationType::class, $user, [
            'validation_groups' => ['register_validation', 'Default'],
            'locales' => [
                'English' => 'en',
                'Russian' => 'ru'
            ]
        ]);

        $form->submit($data);

        $this->assertTrue($form->isSynchronized());
        $this->assertTrue($form->isSubmitted());
        $this->assertTrue($form->isValid());
        $this->assertSame($data['username'], $user->getUsername());
        $this->assertSame($data['email'], $user->getEmail());
        $this->assertSame($data['locale'], $user->getLocale());
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
                    'locale' => 'en',
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
                    'locale' => 'ru',
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
                    'locale' => 'ru',
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