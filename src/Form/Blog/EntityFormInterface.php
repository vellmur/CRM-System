<?php

namespace App\Form\Blog;

use Symfony\Component\OptionsResolver\OptionsResolver;

interface EntityFormInterface
{
    public function configureOptions(OptionsResolver $resolver);
}
