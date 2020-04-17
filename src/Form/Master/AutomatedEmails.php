<?php

namespace App\Form\Master;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Valid;

class AutomatedEmails extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('automatedEmails', CollectionType::class, [
                'required' => false,
                'by_reference' => false,
                'entry_type' => AutomatedEmail::class,
                'constraints' => [
                    new Valid()
                ]
            ]);
    }
}