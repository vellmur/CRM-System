<?php

namespace App\Form\EventListener;

use App\Service\LocationService;
use MenaraSolutions\Geographer\Country;
use MenaraSolutions\Geographer\State;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormInterface;

class OwnerLocationSubscriber implements EventSubscriberInterface
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

        if ($countryCode !== null) {
            $regions = null;
            $cities = null;

            $country = Country::build($countryCode);

            if ($data->getRegion() == null) $data->setCity(null);

            //if country and postal code exists, try to fill data by postalCode
            if (strlen($data->getPostalCode()) > 0) {
                $locations = $this->locationService->getGeoByPostalCode($countryCode, $data->getPostalCode());

                if ($locations) {
                    $region = $country->getStates()->findOne(['name' => $locations['region']['name']]);

                    if ($region) {
                        $data->setRegion($region->getCode());
                        $data->setCity(array_values($locations['cities'])[0]);

                        $regions = [$region->getName() => $region->getCode()];
                        $cities = $locations['cities'];
                    }
                }
            }

            // If after all actions choices for regions/cities not exists
            $regions = $regions ? : $this->locationService->getRegions($countryCode);

            if (!$cities && ($data && $data->getRegion())) $cities = $this->locationService->getCities($countryCode, State::build($data->getRegion())->getName());

            $this->addRegionField($form, $regions, $countryCode);
            $this->addCityField($form, $cities);
        }

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
            $country = Country::build($countryCode);

            $regions = null;
            $cities = null;

            // If postal code exists try to fill data by postalCode
            if ($data['postalCode'] !== '') {
                $locations = $this->locationService->getGeoByPostalCode($countryCode, $data['postalCode']);

                if ($locations) {
                    $region = $country->getStates()->findOne(['name' => $locations['region']['name']]);

                    if (!$region && count($locations['cities'])){
                        $region = $country->getStates()->findOne(['name' => array_keys($locations['cities'])[0]]);
                    }

                    if ($region) {
                        $data['region'] = $region->getCode();
                        $data['city'] = array_values($locations['cities'])[0];

                        $regions = [$region->getName() => $region->getCode()];
                        $cities = $locations['cities'];
                    }
                }
            }

            // If after all actions choices for regions/cities not exists
            $regions = $regions ? : $this->locationService->getRegions($countryCode);

            if ($data['region'] != '' && !$cities) {
                $cities = $this->locationService->getCities($countryCode, State::build($data['region'])->getName());
            } elseif ($data['region'] == '') {
                $cities = null;

                $data['region'] = '';
                $data['city'] = '';
            }

            // Add region/city fields to the form, based on filled location data
            $this->addRegionField($form, $regions, $countryCode);
            $this->addCityField($form, $cities);
            $this->addTimezoneField($form, $countryCode);

            // Update form data
            $event->setData($data);
        }
    }


    /**
     * @param FormInterface $form
     * @param $regions
     * @param $countryCode
     */
    private function addRegionField(FormInterface $form, $regions, $countryCode)
    {
        if ($regions) {
            ksort($regions);
        } else {
            $regions = [];
        }

        $options = $form->get('region')->getConfig()->getOptions();
        $options['choices'] = $regions;
        $options['label'] = $this->locationService->getCountryInfo($countryCode)['territoryType'];
        $form->add('region', ChoiceType::class, $options);
    }


    /**
     * @param FormInterface $form
     * @param $cities
     */
    private function addCityField(FormInterface $form, $cities)
    {
        if ($cities) {
            ksort($cities);
        } else {
            $cities = [];
        }

        $options = $form->get('city')->getConfig()->getOptions();
        $options['choices'] = $cities;
        $form->add('city', ChoiceType::class, $options);
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
