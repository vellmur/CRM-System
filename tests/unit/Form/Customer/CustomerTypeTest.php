<?php

namespace App\Tests\Form\Security;

use App\Entity\Customer\Apartment;
use App\Entity\Customer\Customer;
use App\Form\Customer\CustomerType;
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

class CustomerTypeTest extends TypeTestCase
{
    protected function getExtensions()
    {
        $type = new CustomerType();

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
     * @dataProvider getCustomersValidData
     * @param $data
     * @throws \Exception
     */
    public function testSubmitValidData($data)
    {
        $formCustomer = new Customer();
        $form = $this->factory->create(CustomerType::class, $formCustomer);

        $form->submit($data);

        $customer = new Customer();
        $customer->setFirstName($data['firstname']);
        $customer->setLastName($data['lastname']);
        $customer->setPhone($data['phone']);
        $customer->setEmail($data['email']);
        $customer->setNotes($data['notes']);
        $apartment = new Apartment();
        $apartment->setNumber($data['apartment']['number']);
        $customer->setApartment($apartment);
        $customer->setCreatedAt($formCustomer->getCreatedAt());

        $this->assertEquals($customer, $formCustomer);
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
    public function getCustomersValidData()
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
