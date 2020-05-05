<?php

namespace App\Form\Subscriber;

use App\Form\Client\CardPaymentType;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Contracts\Translation\TranslatorInterface;

class SubscriptionSubscriber implements EventSubscriberInterface
{
    private $factory;

    private $translator;

    public function __construct(FormFactoryInterface $factory, TranslatorInterface $translator)
    {
        $this->factory = $factory;
        $this->translator = $translator;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            FormEvents::PRE_SUBMIT => 'preSubmit'
        ];
    }

    /**
     * @param FormEvent $event
     */
    public function preSubmit(FormEvent $event)
    {
        $form = $event->getForm();
        $data = $event->getData();

        // Add validation for credit card fields, if payment method is credit card
        if (isset($data['module']) && $data['module'] == 1) {
            $options = $form->get('donations')->getConfig()->getOptions();
            $options['constraints'] = [new NotBlank([
                'message' => $this->translator->trans('payment.validation.empty_amount', [], 'messages')
            ])];
            $form->add('donations', ChoiceType::class, $options);
        }

        $cardOptions = $form->get('card')->getConfig()->getOptions();

        // Add/Remove validation for credit card fields, if payment method is credit card
        if (isset($data['method']) && $data['method'] == 1) {
            $cardOptions['constraints'] = [new NotBlank([
                'message' => $this->translator->trans('payment.validation.empty_method', [], 'messages')
            ])];
        } else {
            $cardOptions['validation_groups'] = 'skip_validation';
        }

        $form->add('card', CardPaymentType::class, $cardOptions);

        $event->setData($data);
    }
}