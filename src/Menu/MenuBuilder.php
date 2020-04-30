<?php

namespace App\Menu;

use App\Entity\Client\Client;
use App\Service\ModuleChecker;
use Knp\Menu\FactoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\Translation\TranslatorInterface;

class MenuBuilder
{
    private $factory;

    private $trans;

    private $security;

    private $request;

    private $moduleChecker;

    /**
     * MenuBuilder constructor.
     * @param FactoryInterface $factory
     * @param TranslatorInterface $translator
     * @param Security $security
     * @param RequestStack $request
     * @param ModuleChecker $moduleChecker
     */
    public function __construct(
        FactoryInterface $factory,
        TranslatorInterface $translator,
        Security $security,
        RequestStack $request,
        ModuleChecker $moduleChecker
    ) {
        $this->factory = $factory;
        $this->trans = $translator;
        $this->security = $security;
        $this->request = $request->getCurrentRequest();
        $this->moduleChecker = $moduleChecker;
    }

    /**
     * @param array $options
     * @return \Knp\Menu\ItemInterface
     */
    public function mainMenu(array $options)
    {
        $domain = 'labels';
        $menu = $this->factory->createItem('root');
        $client = $this->security->getUser()->getClient();

        // Master menu
        if ($this->security->isGranted('ROLE_ADMIN')) {
            $this->addMasterMenu($menu);
            $this->addMasterEmailsMenu($menu);
        }

        // Logout for for impersonated user
        if ($this->security->isGranted('ROLE_PREVIOUS_ADMIN')) {
            $menu->addChild('Back to Master', [
                'route' => 'master_dashboard',
                'routeParameters' => [
                    '_switch_user' => '_exit'
                ]
            ])->setAttribute('icon', 'icon-exit3');
        }

        // User menu
        if ($this->security->isGranted('ROLE_OWNER') || $this->security->isGranted('ROLE_EMPLOYEE'))
        {
            $this->addOwnerAccountMenu($menu, $domain);

            // Module Customers Header
            $customersHeader = $this->trans->trans('navigation.module_customers', [], $domain);
            $menu->addChild($customersHeader)->setAttribute('icon', 'icon-menu')->setAttribute('class', 'navigation-header');

            // Module Customers
            $this->addManageCustomersMenu($menu, $domain, $client);
            $this->addEmailsMenu($menu, $domain);
            $this->addOrdersMenu($menu, $domain);
            $this->addPosMenu($menu, $domain);
            $this->addProductsMenu($menu, $domain);
            $this->addVendorsMenu($menu, $domain);
        }

        return $menu;
    }

    /**
     * @return \Knp\Menu\ItemInterface
     */
    public function getAllMenusItems()
    {
        $domain = 'labels';
        $menu = $this->factory->createItem('root');

        $this->addOwnerAccountMenu($menu, $domain);
        $customersHeader = $this->trans->trans('navigation.module_customers', [], $domain);
        $menu->addChild($customersHeader)->setAttribute('icon', 'icon-menu')->setAttribute('class', 'navigation-header');
        $this->addManageCustomersMenu($menu, $domain);
        $this->addEmailsMenu($menu, $domain);
        $this->addOrdersMenu($menu, $domain);
        $this->addPosMenu($menu, $domain);
        $this->addProductsMenu($menu, $domain);
        $this->addVendorsMenu($menu, $domain);

        return $menu;
    }

    /**
     * @param $menu
     */
    private function addMasterMenu(&$menu)
    {
        $master = 'MASTER';
        $menu->addChild($master)->setAttribute('icon', 'icon-grid6')->setAttribute('class', 'has-ul');;
        $menu[$master]->addChild('Dashboard', ['route' => 'master_dashboard'])->setAttribute('icon', 'icon-home');
        $menu[$master]->addChild('Affiliates', ['route' => 'master_affiliates'])->setAttribute('icon', 'icon-tree7');
        $menu[$master]->addChild('Clients', ['route' => 'master_clients'])->setAttribute('icon', 'icon-users');
        $menu[$master]->addChild('Posts', ['route' => 'master_blog'])->setAttribute('icon', 'icon-newspaper');
        $menu[$master]->addChild('Statistics', ['route' => 'master_statistics'])->setAttribute('icon', 'icon-stats-bars2');
        $menu[$master]->addChild('Media Manager', ['route' => 'master_image_manager'])->setAttribute('icon', 'icon-image2');
    }

    /**
     * @param $menu
     */
    private function addMasterEmailsMenu(&$menu)
    {
        $emails = 'EMAIL';
        $menu->addChild($emails)->setAttribute('icon', 'icon-envelope')->setAttribute('class', 'has-ul');;
        $menu[$emails]->addChild('Auto', ['route' => 'master.email.auto'])->setAttribute('icon', 'icon-history');
        $menu[$emails]->addChild('Drafts', ['route' => 'master.email.drafts'])->setAttribute('icon', 'icon-notebook');
        $menu[$emails]->addChild('Compose', ['route' => 'master.email.compose'])->setAttribute('icon', 'icon-compose');
        $menu[$emails]->addChild('Logs', ['route' => 'master.email.log'])->setAttribute('icon', 'icon-book');
    }

    /**
     * @param $menu
     * @param $domain
     */
    private function addOwnerAccountMenu(&$menu, $domain)
    {
        $account = $this->trans->trans('navigation.account.account', [], $domain);
        $menu->addChild($account)->setAttribute('icon', 'icon-cog3')->setAttribute('class', 'has-ul');;
        $menu[$account]->addChild($this->trans->trans('navigation.account.profile', [], $domain), ['route' => 'profile_edit'])->setAttribute('icon', 'icon-user');

        $menu[$account]->addChild($this->trans->trans('navigation.account.users', [], $domain), ['route' => 'user_index'])->setAttribute('icon', 'icon-user-plus');
        $menu[$account]->addChild($this->trans->trans('navigation.account.subscription', [], $domain), ['route' => 'subscription_index'])->setAttribute('icon', 'icon-pencil5');
    }

    /**
     * @param $menu
     * @param $domain
     * @param Client|null $client
     */
    private function addManageCustomersMenu(&$menu, $domain, ?Client $client = null)
    {
        $customers = $this->trans->trans('navigation.customers.manage_customers', [], $domain);
        $menu->addChild($customers)->setAttribute('icon', 'icon-people')->setAttribute('class', 'has-ul');;
        $menu[$customers]->addChild($this->trans->trans('navigation.customers.add', [], $domain), ['route' => 'member_add'])->setAttribute('icon', 'icon-user-plus');

        $filterBy = $this->request->cookies->get('customersFilterBy');
        if ($filterBy === null || $filterBy === 'undefined') $filterBy = 'all';

        $menu[$customers]->addChild($this->trans->trans('navigation.customers.search', [], $domain), [
            'route' => 'customer_list',
            'routeParameters' => [
                'searchBy' => $filterBy
            ]
        ])->setAttribute('icon', 'icon-search4');

        $menu[$customers]->addChild($this->trans->trans('navigation.customers.upload', [], $domain), ['route' => 'members_parse'])->setAttribute('icon', 'icon-file-excel');
    }

    /**
     * @param $menu
     * @param $domain
     */
    private function addEmailsMenu(&$menu, $domain)
    {
        $emails = $this->trans->trans('navigation.emails.emails', [], $domain);
        $menu->addChild($emails)->setAttribute('icon', 'icon-envelop3')->setAttribute('class', 'has-ul');;
        $menu[$emails]->addChild($this->trans->trans('navigation.emails.auto', [], $domain), ['route' => 'member.email.auto'])->setAttribute('icon', 'icon-history');
        $menu[$emails]->addChild($this->trans->trans('navigation.emails.drafts', [], $domain), ['route' => 'member.email.draft'])->setAttribute('icon', 'icon-notebook');
        $menu[$emails]->addChild($this->trans->trans('navigation.emails.compose', [], $domain), ['route' => 'member.email.compose'])->setAttribute('icon', 'icon-compose');
        $menu[$emails]->addChild($this->trans->trans('navigation.emails.logs', [], $domain), ['route' => 'member.email.log'])->setAttribute('icon', 'icon-book');
    }

    /**
     * @param $menu
     * @param $domain
     */
    private function addOrdersMenu(&$menu, $domain)
    {
        $orders = $this->trans->trans('navigation.orders.orders', [], $domain);
        $menu->addChild($orders)->setAttribute('icon', 'icon-cart')->setAttribute('class', 'has-ul');;
        $menu[$orders]->addChild($this->trans->trans('navigation.orders.for_vendors', [], $domain), ['route' => 'vendor_orders'])->setAttribute('icon', 'icon-store2');
    }

    /**
     * @param $menu
     * @param $domain
     */
    private function addPOSMenu(&$menu, $domain)
    {
        $emails = $this->trans->trans('navigation.pos.pos', [], $domain);
        $menu->addChild($emails)->setAttribute('icon', 'icon-basket')->setAttribute('class', 'has-ul');;
        $menu[$emails]->addChild($this->trans->trans('navigation.pos.dashboard', [], $domain), ['route' => 'pos_dashboard'])->setAttribute('icon', 'icon-stats-bars2');
        $menu[$emails]->addChild($this->trans->trans('navigation.pos.entry', [], $domain), ['route' => 'pos_entry'])->setAttribute('icon', 'icon-calculator');
        $menu[$emails]->addChild($this->trans->trans('navigation.pos.orders', [], $domain), [
            'route' => 'pos_orders',
            'routeParameters' => [
                'period' => 'today'
            ]
        ])->setAttribute('icon', 'icon-menu6');
    }

    /**
     * @param $menu
     * @param $domain
     */
    private function addProductsMenu(&$menu, $domain)
    {
        $products = $this->trans->trans('navigation.products.products', [], $domain);
        $menu->addChild($products)->setAttribute('icon', 'icon-cart5')->setAttribute('class', 'has-ul');;
        $menu[$products]->addChild($this->trans->trans('navigation.products.add', [], $domain), ['route' => 'customer_product_add'])->setAttribute('icon', 'icon-cart-add');
        $menu[$products]->addChild($this->trans->trans('navigation.products.pricing', [], $domain), [
            'route' => 'products_pricing',
            'routeParameters' => [
                'category' => $this->request->cookies->get('pricingFilter')
            ]
        ])->setAttribute('icon', 'icon-price-tag');
        $menu[$products]->addChild($this->trans->trans('navigation.products.search', [], $domain), ['route' => 'customer_products_search'])->setAttribute('icon', 'icon-search4');
    }

    /**
     * @param $menu
     * @param $domain
     */
    private function addVendorsMenu(&$menu, $domain)
    {
        $vendors = $this->trans->trans('navigation.vendors.vendors', [], $domain);
        $menu->addChild($vendors)->setAttribute('icon', 'icon-store2')->setAttribute('class', 'has-ul');;
        $menu[$vendors]->addChild($this->trans->trans('navigation.vendors.add', [], $domain), ['route' => 'vendor_add'])->setAttribute('icon', 'icon-user-plus');
        $menu[$vendors]->addChild($this->trans->trans('navigation.vendors.search', [], $domain), ['route' => 'vendor_list'])->setAttribute('icon', 'icon-search4');
    }
}