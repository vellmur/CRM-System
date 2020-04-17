<?php

namespace App\Form\EventListener;

use App\Service\LocationService;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
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
            FormEvents::PRE_SET_DATA => 'preSet',
            FormEvents::POST_SET_DATA => 'postSet'
        );
    }

    /**
     *
     * @param FormEvent $event
     */
    public function preSet(FormEvent $event)
    {
        $form = $event->getForm();
        $data = $event->getData();

        if ($data == null) {
            $options = $form->get('type')->getConfig()->getOptions();;
            $options['data'] = 2;
            $form->add('type', ChoiceType::class, $options);
        } else {
            // If entity is Location, remove addresses fields from the Delivery
            if (stristr(get_class($data), 'Location') && $data->isDelivery()) {
                $form->remove('region');
                $form->remove('city');
                $form->remove('street');
                $form->remove('apartment');
                $form->remove('postalCode');
            }
        }
    }

    /**
     *
     * Here we dynamically set customer location data, based on country/region/city/postalCode
     * @param FormEvent $event
     */
    public function postSet(FormEvent $event)
    {
        $form = $event->getForm();

        $this->addRegionField($form);
    }

    /**
     * @param FormInterface $form
     */
    private function addRegionField(FormInterface $form)
    {
        $country = $form->getConfig()->getOptions()['country'];

        if ($country) {
            $countryInfo = $this->locationService->getCountryInfo($country);

            if ($form->has('region') && strlen($countryInfo['territoryType']) > 0) {
                $options = $form->get('region')->getConfig()->getOptions();
                $options['attr']['placeholder'] = 'customer.address.region';
                $options['label'] = 'customer.address.region';
                $form->add('region', TextType::class, $options);
            }
        }
    }
}
