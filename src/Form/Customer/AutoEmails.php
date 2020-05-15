<?php

namespace App\Form\Customer;

use App\Entity\Building\Building;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Validator\Constraints\Valid;

class AutoEmails extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('autoEmails', CollectionType::class, [
                'required' => false,
                'by_reference' => false,
                'entry_type' => AutoEmail::class,
                'entry_options' => [
                    'label' => 'customer.address.address',
                ],
                'constraints' => [
                    new Valid()
                ]
            ]);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Building::class,
            'translation_domain' => 'labels'
        ]);
    }
}