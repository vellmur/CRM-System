<?php

namespace App\Form\Subscriber;

use App\Entity\Building\Building;
use App\Form\Type\PhoneType;
use App\Service\Localization\PhoneFormat;
use App\Service\Localization\PhoneFormatter;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Contracts\Translation\TranslatorInterface;

class PhoneSubscriber implements EventSubscriberInterface
{
    private $factory;

    private $translator;

    private $security;

    /**
     * PhoneSubscriber constructor.
     * @param FormFactoryInterface $factory
     * @param TranslatorInterface $translator
     * @param Security $security
     */
    public function __construct(FormFactoryInterface $factory, TranslatorInterface $translator, Security $security) {
        $this->factory = $factory;
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
            $phoneFormat = new PhoneFormat($countryCode);

            $phoneLengthError = $this->translator->trans('validation.form.phone_length', [
                '%number%' => $phoneFormat->getDigitsNum()
            ], 'validators');

            $options['attr']['data-mask'] = $phoneFormat->getMask();
            $options['attr']['data-rule-exactLength'] = $phoneFormat->getMaskLength();
            $options['attr']['data-length-message'] = $phoneLengthError;

            $options['constraints'] = [
                new Length([
                    'min' => $phoneFormat->getDigitsNum(),
                    'max' => $phoneFormat->getDigitsNum(),
                    'exactMessage' => $phoneLengthError
                ])
            ];

            $form->getParent()->add('phone', PhoneType::class, $options);
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
            $phoneFormat = new PhoneFormat($countryCode);

            if (strlen($phone) == strlen($phoneFormat->getPhonePrefix())) {
                // If phone value contain only phone code, don't validate field (because field is empty)
                $options = $form->getConfig()->getOptions();
                $options['validation_groups'] = false;
                $form->getParent()->add('phone', PhoneType::class, $options);
                $event->setData(null);
            } else {
                $phoneFormatter = new PhoneFormatter($phoneFormat, $phone);
                $unmaskedPhone = $phoneFormatter->getCleanPhoneNumber();
                $event->setData($unmaskedPhone);
            }
        }
    }
}
