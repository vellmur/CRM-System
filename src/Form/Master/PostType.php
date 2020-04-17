<?php

namespace App\Form\Master;

use App\Form\Blog\EntityFormInterface;
use App\Form\Blog\PostBaseType;
use App\Entity\Master\Post;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PostType extends PostBaseType implements EntityFormInterface
{
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Post::class
        ]);
    }
}
