<?php

namespace App\Form\Type;

use App\Entity\Client\Client;
use App\Entity\Media\Image;
use App\Repository\ImageRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;

class ImageType extends AbstractType
{
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        /** @var Client $client */
        $client = $this->security->getUser()->getClient();

        $resolver->setDefaults([
            'required' => false,
            'translation_domain' => 'labels',
            'class' => Image::class,
            'query_builder' => function (ImageRepository $er) use ($client) {
                return $er->createQueryBuilder('image')
                    ->where('image.client = :client')
                    ->setParameter('client', $client === null ? null : $client->getId());
            },
            'attr' => [
                'class' => 'hidden',
                'label' => false
            ]
        ]);
    }

    public function getParent()
    {
        return EntityType::class;
    }
}