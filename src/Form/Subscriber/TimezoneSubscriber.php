<?php

namespace App\Form\Subscriber;

use App\Service\LocationService;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormInterface;

class TimezoneSubscriber implements EventSubscriberInterface
{
    private $factory;

    private $locationService;

    /**
     * TimezoneSubscriber constructor.
     * @param FormFactoryInterface $factory
     * @param LocationService $locationService
     */
    public function __construct(FormFactoryInterface $factory, LocationService $locationService)
    {
        $this->factory = $factory;
        $this->locationService = $locationService;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            FormEvents::POST_SET_DATA => 'postSet',
            FormEvents::PRE_SUBMIT => 'preSubmit'
        ];
    }

    /**
     * Here we dynamically set owner location data, based on country/region/city/postalCode
     * @param FormEvent $event
     */
    public function postSet(FormEvent $event)
    {
        $form = $event->getForm();
        $data = $event->getData();

        $countryCode = $data && $data->getAddress() && $data->getAddress()->getCountry() ? $data->getAddress()->getCountry() : null;
        $this->addTimezoneField($form, $countryCode);
    }

    /**
     * Here we dynamically set owner location data, based on country/region/city/postalCode
     *
     * @param FormEvent $event
     */
    public function preSubmit(FormEvent $event)
    {
        $form = $event->getForm();
        $data = $event->getData();

        // All this have must happens only if some country selected
        if ($countryCode = isset($data['address']['country']) ? $data['address']['country'] : null) {
            $this->addTimezoneField($form, $countryCode);
        }
    }

    /**
     * @param FormInterface $form
     * @param string|null $countryCode
     */
    public function addTimezoneField(FormInterface $form, ?string $countryCode = null)
    {
        $form->add('timezone', ChoiceType::class, [
            'required' => false,
            'choices' => $this->locationService->getTimezonesList($countryCode),
            'attr' => [
                'class' => 'select'
            ],
            'placeholder' => ''
        ]);
    }
}
