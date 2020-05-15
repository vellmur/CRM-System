<?php

namespace App\Form\Subscriber;

use App\Entity\Building\Building;
use App\Form\Type\PhoneType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Validator\Constraints\Length;
use App\Data\CountryInfo;
use Symfony\Contracts\Translation\TranslatorInterface;

class PhoneSubscriber implements EventSubscriberInterface
{
    private $factory;

    private $countriesInfo;

    private $translator;

    private $security;

    public function __construct(FormFactoryInterface $factory, TranslatorInterface $translator, Security $security)
    {
        $this->factory = $factory;
        $this->countriesInfo = new CountryInfo();
        $this->translator = $translator;
        $this->security = $security;
    }

    public static function getSubscribedEvents()
    {
        return [
            FormEvents::PRE_SET_DATA => 'preSet',
            FormEvents::PRE_SUBMIT => 'preSubmit'
        ];
    }

    /**
     * @param FormEvent $event
     * @throws \Exception
     */
    public function preSet(FormEvent $event)
    {
        $form = $event->getForm();
        $options = $form->getConfig()->getOptions();

        if (isset($options['attr']['data-mask'])) {
            return;
        }

        /** @var Building|null $building */
        $building = $this->security->getUser() ? $this->security->getUser()->getBuilding() : null;

        if ($building && $building->getAddress() && $countryCode = $building->getAddress()->getCountry()) {
            $phoneFormat = $this->countriesInfo->getPhoneFormat($countryCode);

            $phoneLengthError = $this->translator->trans('validation.form.phone_length', [
                '%number%' => $phoneFormat['length']
            ], 'validators');

            if ($phoneFormat) {
                $options['attr']['data-mask'] = $phoneFormat['mask'];
                $options['attr']['data-rule-exactLength'] = $phoneFormat['validationLength'];
                $options['attr']['data-length-message'] = $phoneLengthError;

                $options['constraints'] = [
                    new Length([
                        'min' => $phoneFormat['unmaskedLength'],
                        'max' => $phoneFormat['unmaskedLength'],
                        'exactMessage' => $phoneLengthError
                    ])
                ];

                $form->getParent()->add('phone', PhoneType::class, $options);
            }
        }
    }

    /**
     * @param FormEvent $event
     * @throws \Exception
     */
    public function preSubmit(FormEvent $event)
    {
        $form = $event->getForm();
        $phone = $event->getData();

        /** @var Building|null $building */
        $building = $this->security->getUser() ? $this->security->getUser()->getBuilding() : null;

        if ($building && $building->getAddress() && $countryCode = $building->getAddress()->getCountry()) {
            $unmaskedPhone = $this->countriesInfo->getUnmaskedPhone($phone, $countryCode);

            if ($unmaskedPhone) {
                $event->setData($unmaskedPhone);
            } else {
                // If phone value contain only phone code, don't validate field (because field is empty)
                $phoneCode = $this->countriesInfo->getPhoneFormat($countryCode)['code'];

                // Replace phone field
                if (strlen(($phone)) == strlen($phoneCode)) {
                    $options = $form->getConfig()->getOptions();
                    $options['validation_groups'] = false;
                    $form->getParent()->add('phone', PhoneType::class, $options);
                }
            }
        }
    }
}
