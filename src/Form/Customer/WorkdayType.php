<?php

namespace App\Form\Customer;

use App\Entity\Customer\Workday;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class WorkdayType extends AbstractType
{
    function hoursRange($lower = 0, $upper = 23, $step = 1)
    {
        $times = [];

        foreach(range($lower, $upper, $step) as $increment) {
            $increment = number_format($increment, 2);
            list($hour, $minutes) = explode('.', $increment);
            $date = new \DateTime($hour . ':' . $minutes * .6);
            $time = $date->format('g:i A');
            $times[$time] = $time;
        }

        return $times;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $times = $this->hoursRange(0, 24, 0.5);

        $builder
            ->add('weekday', HiddenType::class)
            ->add('startTime', ChoiceType::class, [
                'choices' =>  array_flip($times),
                'attr' => [
                    'class' => 'select'
                ],
                'required' => false,
                'placeholder' => ''
            ])
            ->add('duration', TextType::class, [
                'attr' => [
                    'class' => 'form-control'
                ],
                'required' => false
            ]);

        // Add event listener on PRE_SET_DATA event
        $builder->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event) {
            $product = $event->getData();

            // If data is not null, change checkbox labels to show the day of Week
            if ($product) {
                $form = $event->getForm();

                $form->add('isActive', CheckboxType::class, [
                    'required' => false,
                    'label' => $product->getWeekdayName(),
                    'attr' => [
                        'class' => 'styled'
                    ]
                ]);
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Workday::class
        ]);
    }
}