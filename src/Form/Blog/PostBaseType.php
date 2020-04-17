<?php

namespace App\Form\Blog;

use App\Entity\Media\Image;
use App\Form\Type\ImageType;
use App\Repository\ImageRepository;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

abstract class PostBaseType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, [
                'translation_domain' => 'labels',
                'required' => false,
                'label' => 'blog.title',
                'label_attr' => [
                    'class' => 'col-md-2 col-sm-3 col-xs-5 control-label'
                ],
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'blog.title'
                ]
            ])
            ->add('text', CKEditorType::class, [
                'translation_domain' => 'labels',
                'required' => false,
                'label' => 'blog.text',
                'label_attr' => [
                    'class' => 'col-md-2 col-sm-3 col-xs-5 control-label'
                ],
                'attr' => [
                    'rows' => 7,
                    'data-type' => 'string',
                    'class' => 'form-control',
                    'placeholder' => 'blog.text'
                ]
            ])
            ->add('isActive', CheckboxType::class, [
                'translation_domain' => 'labels',
                'required' => false,
                'label' => 'table.active',
                'label_attr' => [
                    'class' => 'col-md-2 col-sm-3 col-xs-5 control-label'
                ],
                'attr' => [
                    'class' => 'switchery'
                ]
            ])
            ->add('thumb', ImageType::class, [
                'label' => 'blog.thumb',
                'label_attr' => [
                    'class' => 'col-md-2 col-sm-3 col-xs-5 control-label'
                ]
            ]);
    }
}