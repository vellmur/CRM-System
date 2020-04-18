<?php

namespace App\Form\EventListener;

use App\Entity\Client\Client;
use App\Entity\Customer\Location;
use App\Entity\Customer\Customer;
use App\Entity\Customer\Workday;
use App\Form\CardPaymentType;
use App\Form\Customer\AddressType;
use App\Manager\MemberManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use App\Entity\Client\PaymentSettings;
use Symfony\Contracts\Translation\TranslatorInterface;

class RenewSubscriber implements EventSubscriberInterface
{
    private $factory;

    private $manager;

    private $imageProvider;

    private $translator;

    public function __construct(
        FormFactoryInterface $factory,
        MemberManager $manager,
        TranslatorInterface $translator
    ) {
        $this->factory = $factory;
        $this->manager = $manager;
        $this->imageProvider = '$imageProvider';
        $this->translator = $translator;
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
     * @param FormEvent $event
     */
    public function postSet(FormEvent $event)
    {
        $form = $event->getForm();
        $client = $form->getConfig()->getOptions()['client'];

        date_default_timezone_set($client->getTimezone());

        /** @var Customer $customer */
        $customer = $form->get('member')->getData();

        $this->addLocation($form);
        $this->addPaymentMethods($form);

        // Pre-set customer data to renewal form, if customer data exists (profile page)
        if ($customer) {
            // Pre-set addresses
            $form->get('locationAddress')->setData($customer->getAddressByType('DELIVERY'));
            $form->get('billingAddress')->setData($customer->getAddressByType('BILLING'));
        }

        $this->addShares($form);
        $this->addProducts($form);
    }

    /**
     * @param FormEvent $event
     */
    public function preSubmit(FormEvent $event)
    {
        $form = $event->getForm();
        $data = $event->getData();

        // Remove products qty from form (form can`t contain extra fields). Data still exists in request.
        if (isset($data['parentFrame'])) { unset($data['parentFrame']); }
        if (isset($data['shareQty'])) { unset($data['shareQty']); }
        if (isset($data['productQty'])) { unset($data['productQty']); }

        if ($form->get('member')->getData()) {
            // Add validation error, if customer did'nt choose any product
            if (!isset($data['shares']) && !isset($data['products'])) {
                $form->addError(new FormError('Please add to a cart one or multiple products', null, [], null, 'EmptyProduct'));
            }
        }

        $paymentMethods = PaymentSettings::getMethodsNames();

        if (isset($data['location'])) {
            $location = $this->manager->getLocationById($data['location']);

            // Remove addresses validation (not required, if isn`t delivery and payment method isn`t credit card)
            if (!$location->isDelivery() && (isset($data['method']) && $paymentMethods[$data['method']] != 'card')) {
                $options = $form->get('locationAddress')->getConfig()->getOptions();
                $options['validation_groups'] = 'not_check';
                $form->add('locationAddress', AddressType::class, $options);
            }
        }

        // Remove validation from billing address, if extra address not checked (billing same as delivery address)
        if (!isset($data['isNeedBilling'])) {
            $options = $form->get('billingAddress')->getConfig()->getOptions();
            $options['validation_groups'] = 'not_check';
            $form->add('billingAddress', AddressType::class, $options);
        }

        // Remove validation form credit card fields, if payment method isn't credit card
        if (isset($data['method'])) {
            if ($paymentMethods[$data['method']] != 'card') {
                $options = $form->get('card')->getConfig()->getOptions();
                $options['validation_groups'] = 'not_check';
                $form->add('card', CardPaymentType::class, $options);
            }
        }

        $event->setData($data);
    }

    /**
     * @param FormInterface $form
     */
    public function addShares(FormInterface $form)
    {
        $options = $form->get('shares')->getConfig()->getOptions();

        /** @var Client $client */
        $client = $form->getConfig()->getOptions()['client'];

        $shares = $this->manager->getShares($client, true);

        foreach ($shares as $key => $share) {
            $options['choices'][$key] = $share->getId();

            $options['choice_attr'][$key] = [
                'data-price' => $share->getPrice(),
                'data-name' => $share->getName(),
                'data-description' => $share->getDescription(),
                'autocomplete' => 'off',
                'class' => 'customer-product hidden',
                'data-rule-checkRequired' => 'true',
                'data-empty-error' => $this->translator->trans('renewal.product_required', [], 'validators')
            ];
        }

        $form->add('shares', ChoiceType::class, $options);
    }

    /**
     * @param FormInterface $form
     */
    public function addProducts(FormInterface $form)
    {
        $options = $form->get('products')->getConfig()->getOptions();

        /** @var Client $client */
        $client = $form->getConfig()->getOptions()['client'];

        $products = $this->manager->getCustomerProducts($client);

        $filters = [];

        foreach ($products as $key => $product) {
            $options['choices'][$key] = $product->getId();

            $productName = $product->getName();
            $productDescription = $product->getDescription() ? $product->getDescription() : $productName;
            $productPrice = $product->isPos() && $product->getDeliveryPrice() ? $product->getDeliveryPrice() : $product->getPrice();
            $pricePer = $product->isPos() ? ($product->isPayByItem() ? 'per_item' : 'per_' . $client->getWeightName()) : 'each';

            $tags = [];

            foreach ($product->getTags() as $i => $productTag) {
                $tags[] = $productTag->getTag()->getName();
                if (!in_array($productTag->getTag()->getName(), $filters)) $filters[] = $productTag->getTag()->getName();
            }

            sort($tags);

            $options['choice_attr'][$key] = [
                'data-price' => $productPrice,
                'data-name' => $productName,
                'data-description' => $productDescription,
                'data-payByItem' => (!$product->isPos() || $product->isPayByItem()) ? '1' : '0',
                'data-pricePer' => $pricePer,
                'data-filters' => implode(',', $tags),
                'data-rule-checkRequired' => 'true',
                'data-empty-error' => $this->translator->trans('renewal.product_required', [], 'validators'),
                'class' => 'customer-product hidden',
                'autocomplete' => 'off'
            ];

            if ($product->getImage()) {
                $options['choice_attr'][$key]['data-image-path'] = $this->imageProvider->generatePublicUrl($product->getImage(), 'reference');
            }
        }

        $options['attr']['data-filters'] = $filters;

        $form->add('products', ChoiceType::class, $options);
    }

    /**
     * @param FormInterface $form
     */
    public function addLocation(FormInterface $form)
    {
        /** @var Client $client */
        $client = $form->getConfig()->getOptions()['client'];

        $locations = $this->manager->getActiveLocations($client);

        $options = $form->get('location')->getConfig()->getOptions();

        // Get all suspended weeks by client
        $suspendedWeeks = $weeks = $this->manager->getSuspendedWeeks($client);

        foreach ($locations as $location) {
            $options['choices'][$location->getName()] = $location->getId();

            $ordersDates = $this->getOrdersDates($location->getWorkdays(), $suspendedWeeks, $client->getOrderTime());

            $options['choice_attr'][$location->getName()] = [
                'class' => 'styled',
                'data-location' => strtolower($location->getTypeName()),
                'data-description' => $location->getDescription(),
                'data-address' => $this->setAddressFormat($location),
                'data-ordersDates' => json_encode($ordersDates),
                'data-nextOrderDate' => $this->getDateOfNextOrder($ordersDates, $client->isSameDayOrdersAllowed())
            ];

            $options['choice_label'] = function ($choice, $key, $value) {
                if ($key == 'HOME DELIVERY') {
                    return mb_strtoupper($this->translator->trans('membership.renew.location.home_delivery', [], 'labels'));
                }

                return $key;
            };
        }

        // Pre-set location, if only one location exists
        if (count($locations) == 1) $options['data'] = $locations[0]->getId();

        $form->add('location', ChoiceType::class, $options);
    }

    /**
     * @param FormInterface $form
     */
    public function addPaymentMethods(FormInterface $form)
    {
        /** @var Client $client */
        $client = $form->getConfig()->getOptions()['client'];

        $paymentSettings = $this->manager->getPaymentSettings($client);
        $methods = PaymentSettings::getMethodsNames();

        foreach ($methods as $id => $method) {
            if (isset($paymentSettings[$id]) && !$paymentSettings[$id]->isActive()) {
                unset($methods[$id]);
            }
        }

        $options = [
            'required' => true,
            'choices' => array_flip($methods),
            'label_attr' => [
                'class' => 'control-label'
            ],
            'choice_attr' => function($choice) use ($paymentSettings, $methods) {
                return [
                    'class' => 'styled',
                    'data-type' => $methods[$choice],
                    'data-description' => isset($paymentSettings[$choice]) ? $paymentSettings[$choice]->getDescription() : null,
                    'data-rule-cardValidation' => 'true',
                    'autocomplete' => 'off'
                ];
            },
            'choice_label' => function ($choice, $key, $value) {
                return $this->translator->trans('membership.renew.payment_methods.' . $key, [], 'labels');
            },
            'constraints' => [
                new NotBlank(['message' => 'You need to select one of the payment methods.'])
            ],
            'label' => 'membership.renew.payment_method',
            'expanded' => true,
            'placeholder' => false
        ];

        // Pre-set payment method, if only one method exists
        if (count($methods) == 1) $options['data'] = array_keys($methods)[0];

        $form->add('method', ChoiceType::class, $options);
    }

    /**
     * Get orders dates (datetime) from active workdays (weekday) of client work locations
     *
     * @param \Doctrine\Common\Collections\Collection|Workday $workdays
     * @param $suspendedWeeks
     * @param $clientOrderTime string
     * @return array
     */
    public function getOrdersDates($workdays, $suspendedWeeks, $clientOrderTime)
    {
        $daysOfWeek = [];

        // Get active week days
        foreach ($workdays as $workday) {
            if ($workday->isActive()) {
                $daysOfWeek[$workday->getWeekday()] = $workday->getWeekdayName();
            }
        }

        $dates = [];

        $orderTime = explode(':', $clientOrderTime); // get hours and minutes from client order time

        // Find dates of the following weekdays, so they are used as orders dates and with $clientOrderTime
        foreach ($daysOfWeek as $day) {
            $now = new \DateTime();
            $orderDate = $day != $now->format('l') ? $now->modify('next ' . $day) : $now;

            // Suspend all forward weeks, if client suspended pickup week
            while ($this->manager->isDateSuspended($suspendedWeeks, $orderDate)) {
                $orderDate->modify('+7 days');
            }

            $orderDate->setTime($orderTime[0], $orderTime[1]);
            $dates[] = $orderDate->format('Y-m-d H:i:s');
        }

        // Sort dates from closest to latest
        usort($dates, function($a, $b) {
            return strtotime($a) - strtotime($b);
        });

        return $dates;
    }

    /**
     * Get closest order date from existed orders dates of location
     *
     * @param $ordersDates
     * @param $sameDayOrdersAllowed
     * @return null
     */
    public function getDateOfNextOrder($ordersDates, $sameDayOrdersAllowed)
    {
        $now = new \DateTime();

        $nextOrderDate = null;

        foreach ($ordersDates as $key => $date) {
            $orderDate = new \DateTime($date);

            if ($orderDate >= $now) {
                $nextOrderDate = $orderDate;

                if (!$sameDayOrdersAllowed) {
                    $nextOrderDate->modify('+1 day');
                }

                break;
            }
        }

        return $nextOrderDate->format('Y-m-d H:i:s');
    }

    /**
     * @param Location $location
     * @return string
     */
    public function setAddressFormat(Location $location)
    {
        $address = '';

        if ($location->getStreet()) $address .= $location->getStreet() . ' ';

        if ($location->getApartment()) {
            $address .= $location->getStreet() . '<br/>';
        } elseif (strlen($address) > 0) {
            $address .= '<br/>';
        }

        if ($location->getCity() && $location->getRegion()) {
            $address .= $location->getCity() . ', ' . $location->getRegion() . ' ';
        } else {
            $address .= $location->getCity() ? $location->getCity() : $location->getRegion() . ' ';
        }

        if ($location->getPostalCode()) $address .= $location->getPostalCode();

        return $address;
    }
}