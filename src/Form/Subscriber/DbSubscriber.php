<?php

namespace App\Form\Subscriber;

use App\Entity\City;
use App\Service\LocationService;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormInterface;

class DbSubscriber implements EventSubscriberInterface
{
    private $factory;

    private  $locationService;

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

        if ($data == null) {
            return;
        }

        if (stristr(get_class($data), 'Address')) {
            $country = $data->getMember()->getClient()->getCountry();
        } else {
            $country = $data->getCountry();
        }

        if ($country) {
            $regions = [];
            $cities = [];

            if ($data->getRegion() == null) $data->setCity(null);

            //if country and postal code exists, try to fill data by postalCode
            if (strlen($data->getPostalCode()) > 0) {
                $city = $this->locationService->getGeolocationData([$country->getName(), $data->getPostalCode()], 'locality');

                if ($city && $city->getRegion()->getCountry()->getId() == $country->getId()) {
                    $data->setRegion($city->getRegion());
                    $data->setCity($city);

                    $regions = [$city->getRegion()];
                    $cities = [$city];
                } else {
                    $data->setRegion(null);
                    $data->setCity(null);
                }
            } else if ($data->getCity()) {
                // else try to fill postalCode by city
                $data->setPostalCode($this->setPostalCodeField($form, $data->getCity()));
            }

            if (count($regions) == 0) $regions = $country->getRegions();
            if (count($cities) == 0 && $data->getRegion()) $cities = $data->getRegion()->getCities();

            $this->addRegionField($form, $regions);
            $this->addCityField($form, $cities);
        }
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

        // For customer address entity we haven`t country field, so we can country from parent Client entity
        if (stristr(get_class($form->getData()), 'Address')) {
            $country = $form->getData()->getMember()->getClient()->getCountry();
        } else {
            $country = array_key_exists('country', $data) && $data['country'] != '' ? $this->locationService->findLocation('Country', $data['country']) : null;
        }

        // All this have must happens only if some country selected
        if ($country) {
            $regions = [];
            $cities = [];

            // If country was changed, set location data to null (in owner profile page)
            if ($form->getData()->getCountry() && $form->getData()->getCountry() != $country) {
                $data['region'] = '';
                $data['city'] = '';
                // If postal code exists and its new postal code(user typed it right now), try to fill data by postalCode
            } else if ($data['postalCode'] != '' && $data['postalCode'] != $form->getData()->getPostalCode()) {
                $city = $this->locationService->getGeolocationData([$country, $data['postalCode']], 'locality');

                if ($city && $city->getRegion()->getCountry()->getId() == $country->getId()) {
                    $data['region'] = $city->getRegion()->getId();
                    $data['city'] = $city->getId();

                    $regions = [$city->getRegion()];
                    $cities = [$city];
                } else {
                    $data['region'] = '';
                    $data['city'] = '';
                }
                // else if city exists and city is right(city region equal to user region) set postal code
            } else if ($data['city'] != '') {
                $city = $this->locationService->findLocation('City', $data['city']);

                if ($city->getRegion()->getId() == $data['region']) {
                    $postalCode = $this->setPostalCodeField($form, $city);
                    if ($postalCode) $data['postalCode'] = $postalCode;
                }
            }

            // If after all actions choices for regions/cities not exists
            if (count($regions) == 0) $regions = $country->getRegions();

            // Find cities by selected region
            if (count($cities) == 0 && $data['region'] != '') {
                $region = $this->locationService->findLocation('Region', $data['region']);

                if ($region) $cities = $region->getCities();
            }

            // Add region/city fields to the form, based on filled location data
            $this->addRegionField($form, $regions);
            $this->addCityField($form, $cities);

            // Update form data
            $event->setData($data);
        }
    }

    /**
     * @param FormInterface $form
     * @param $regions
     */
    private function addRegionField(FormInterface $form, $regions)
    {
        $options = $form->get('region')->getConfig()->getOptions();
        $options['choices'] = $regions;
       // if (count($regions) === 1) $options['placeholder'] = false;
        $form->add('region', EntityType::class, $options);
    }

    /**
     * @param FormInterface $form
     * @param $cities
     */
    private function addCityField(FormInterface $form, $cities)
    {
        $options = $form->get('city')->getConfig()->getOptions();
        $options['choices'] = $cities;
      //  if (count($cities) === 1) $options['placeholder'] = false;
        $form->add('city', EntityType::class, $options);
    }

    /**
     * @param FormInterface $form
     * @param City $city
     * @return bool|null
     */
    public function setPostalCodeField(FormInterface $form, City $city)
    {
        $postalCode = $this->locationService->getGeolocationData([$city->getRegion()->getCountry()->getName(), $city->getName()], 'postal_code');

        if ($postalCode === false) $postalCode = '';

        if ($postalCode !== '') {
            $options = $form->get('postalCode')->getConfig()->getOptions();
            $options['data'] = $postalCode;
            $form->add('postalCode', TextType::class, $options);
        }

        return $postalCode;
    }
}
