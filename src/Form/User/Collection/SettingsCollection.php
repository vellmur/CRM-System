<?php

namespace App\Form\User\Collection;

use App\Form\User\ModuleSettingsType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;

class SettingsCollection extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('settings', CollectionType::class, [
                'entry_type' => ModuleSettingsType::class,
                'label' => false,
                'allow_add' => true,
                'prototype' => true
            ]);
    }
}