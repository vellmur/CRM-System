<?php

namespace App\Tests\Form\Security;

use App\Entity\Owner\Apartment;
use App\Entity\Owner\Owner;
use App\Form\Owner\OwnerType;
use App\Form\Subscriber\PhoneSubscriber;
use App\Form\Type\PhoneType;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class OwnerTypeTest extends TypeTestCase
{
    protected function getExtensions()
    {
        $type = new OwnerType();

        return [
            new PreloadedExtension([$type], []),
            new PreloadedExtension([$this->getPhoneType()], []),
            new ValidatorExtension($this->getValidatorExtension()),
        ];
    }

    /**
     * @return PhoneType
     */
    private function getPhoneType()
    {
        $formFactory = $this->createMock(FormFactoryInterface::class);
        $translator = $this->createMock(Translator::class);
        $security = $this->createMock(Security::class);
        $phoneSubscriber = new PhoneSubscriber($formFactory, $translator, $security);

        return new PhoneType($phoneSubscriber);
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
     * @dataProvider getOwnersValidData
     * @param $data
     * @throws \Exception
     */
    public function testSubmitValidData($data)
    {
        $formOwner = new Owner();
        $form = $this->factory->create(OwnerType::class, $formOwner);

        $form->submit($data);

        $owner = new Owner();
        $owner->setFirstName($data['firstname']);
        $owner->setLastName($data['lastname']);
        $owner->setPhone($data['phone']);
        $owner->setEmail($data['email']);
        $owner->setNotes($data['notes']);
        $apartment = new Apartment();
        $apartment->setNumber($data['apartment']['number']);
        $owner->setApartment($apartment);
        $owner->setCreatedAt($formOwner->getCreatedAt());

        $this->assertEquals($owner, $formOwner);
        $this->assertTrue($form->isSynchronized());
        $this->assertTrue($form->isSubmitted());
        $this->assertTrue($form->isValid());

        $view = $form->createView();
        $children = $view->children;

        foreach (array_keys($data) as $key) {
            $this->assertArrayHasKey($key, $children);
        }
    }

    /**
     * @return array
     */
    public function getOwnersValidData()
    {
        return [
            [
                [
                    'firstname' => 'Jack',
                    'lastname' => 'Rob',
                    'phone' => '+380931234789',
                    'email' => 'jackrob@gmail.com',
                    'apartment' => [
                        'number' => 1
                    ],
                    'notes' => 'Test note about Jack Rob.'
                ]
            ],
            [
                [
                    'firstname' => 'Дима',
                    'lastname' => 'Гордей',
                    'phone' => null,
                    'email' => 'dimagordey@mail.ru',
                    'apartment' => [
                        'number' => 2
                    ],
                    'notes' => 'Это еще один клиент'
                ]
            ],
            [
                [
                    'firstname' => 'Tom',
                    'lastname' => 'Hardy',
                    'phone' => '+380639763428',
                    'email' => null,
                    'apartment' => [
                        'number' => 3
                    ],
                    'notes' => 'Tom Hardy have no email.'
                ]
            ]
        ];
    }
}
