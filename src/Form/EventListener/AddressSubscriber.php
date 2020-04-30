<?php

namespace App\Form\EventListener;

use App\Service\LocationService;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormInterface;

class AddressSubscriber implements EventSubscriberInterface
{
    private $factory;

    private $locationService;

    public function __construct(FormFactoryInterface $factory, LocationService $locationService)
    {
        $this->factory = $factory;
        $this->locationService = $locationService;
    }

    public static function getSubscribedEvents()
    {
        return array(
            FormEvents::POST_SET_DATA => 'postSet',
            FormEvents::PRE_SUBMIT => 'preSubmit'
        );
    }

    /**
     *
     * Here we dynamically set customer location data, based on country/region/city/postalCode
     * @param FormEvent $event
     */
    public function postSet(FormEvent $event)
    {
        $form = $event->getForm();
        $data = $event->getData();

        $countryCode = $data && $data->getCountry() ? $data->getCountry() : null;
        $this->addTimezoneField($form, $countryCode);
    }

    /**
     *
     * Here we dynamically set customer location data, based on country/region/city/postalCode
     *
     * @param FormEvent $event
     */
    public function preSubmit(FormEvent $event)
    {
        $form = $event->getForm();
        $data = $event->getData();

        $countryCode = array_key_exists('country', $data) ? $data['country'] : null;

        // All this have must happens only if some country selected
        if ($countryCode && $countryCode !== '') {
            $this->addTimezoneField($form, $countryCode);
            $event->setData($data);
        }
    }

    /**
     * @param FormInterface $form
     * @param $countryCode
     */
    public function addTimezoneField(FormInterface $form, $countryCode)
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
