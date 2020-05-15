<?php

namespace App\Form\Type;

use App\Entity\Building\Building;
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
        /** @var Building $building */
        $building = $this->security->getUser()->getBuilding();

        $resolver->setDefaults([
            'required' => false,
            'translation_domain' => 'labels',
            'class' => Image::class,
            'query_builder' => function (ImageRepository $er) use ($building) {
                return $er->createQueryBuilder('image')
                    ->where('image.building = :building')
                    ->setParameter('building', $building === null ? null : $building->getId());
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