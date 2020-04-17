<?php

namespace App\Service;

use App\Entity\Customer\Email\EmailRecipient;
use App\Entity\User\Device;
use App\Entity\User\PageView;
use App\Entity\Master\Email\Recipient;
use App\Entity\ModuleAccess;
use App\Manager\DeviceManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserInterface;

class PageViewSaver
{
    private $pageNames = [
        'Website' => [
            'landing',
            'blog',
            'mission',
            'faq',
            'login',
            'register',
            'ebook'
        ],
        'Owner' => [
            'profile',
            'users',
            'settings',
            'translation',
            'subscription'
        ],
        'Crops' => [
            'plants',
            'gardens',
            'notes',
            'crops',
            'dashboard',
            'trays',
            'plants in garden',
            'harvest date',
            'end of garden',
            'seeds',
            'seed inventory',
            'tasks',
            'garden report',
            'garden history',
            'now growing',
            'seed report',
            'what we grew',
            'where to plant'
        ],
        'Customers' => [
            'customer add',
            'customer locations',
            'customer search',
            'customer upload',
            'auto emails',
            'draft emails',
            'compose email',
            'email logs',
            'available plants',
            'for members',
            'for vendors',
            'harvest list',
            'packaging',
            'pos dashboard',
            'pos entry',
            'pos orders',
            'product add',
            'product pricing',
            'product search',
            'vendor add',
            'vendor search',
            'profile',
            'share day report',
            'membership login'
        ],
        'Company' => [
            'blog',
            'manage website',
            'media gallery',
            'widgets',
            'editor',
            'invoices',
            'merchant',
            'statistics'
        ]
    ];

    private $em;

    private $envService;

    private $deviceManager;

    public function __construct (EntityManagerInterface $em, EnvironmentService $envService, DeviceManager $deviceManager) {
        $this->em = $em;
        $this->envService = $envService;
        $this->deviceManager = $deviceManager;
    }

    /**
     * @param UserInterface|null $user
     * @param $url
     * @param $moduleName
     * @param null $deviceId
     * @return int|null
     * @throws \Exception
     */
    public function savePageView(?UserInterface $user, $url, $moduleName, $deviceId = null)
    {
        try {
            if ($this->notSaveAblePage($url)) {
                return null;
            }

            if ($pageName = $this->getPageName($moduleName, $url)) {
                $device = $this->getOrCreateUserDevice($user, $deviceId);

                if ($device !== null) {
                    $moduleId = $moduleName ? ModuleAccess::getModuleId($moduleName) : null;
                    $this->saveView($device->getId(), $url, $moduleId, $pageName);

                    return $device->getId();
                }
            }
        } catch (\Exception $e) {
            throw $e;
        }

        return $deviceId;
    }

    /**
     * @param $deviceId
     * @param $url
     * @param $moduleId
     * @param $pageName
     * @throws \Exception
     */
    private function saveView($deviceId, $url, $moduleId, $pageName)
    {
        $this->em->getRepository(PageView::class)->saveView($deviceId, $url, $moduleId, $pageName);
    }

    /**
     * @param $link
     * @return bool
     */
    private function notSaveAblePage($link)
    {
        $skipSaveView = ['widget-load', 'api', 'logout', '_profiler', '_wdt', 'routing', 'master'];

        return $this->strposa($link, $skipSaveView);
    }

    /**
     * @param $module
     * @param $link
     * @return null|string
     */
    private function getPageName($module, $link)
    {
        $module = ucfirst($module);

        $pageName = null;

        if ($link == '/') {
            return 'landing';
        }

        // If given link is promotional link
        if (strstr($link, 'promo_link')) {
            $parts = parse_url($link);
            parse_str($parts['query'], $query);

            return $query['promo_link'];
        }

        $slugs = explode('/', strtok($link,'?'));
        $itemName = strlen($slugs[count($slugs) - 1]) > 0 ? $slugs[count($slugs) - 1] : $slugs[count($slugs) - 2];

        // If item name contain numbers (token, id, etc...), set name to previous slug
        if (preg_match('#[0-9]#', $itemName)) {
            $itemName = $slugs[count($slugs) - 2];
        }

        if (stristr($itemName, '-')) $itemName = str_replace('-', ' ', $itemName);

        if (!$module) $module = 'Website';

        $modulePages = array_flip($this->pageNames[$module]);

        if (isset($modulePages[$itemName])) {
            $pageKey = $modulePages[$itemName];
            $pageName = $this->pageNames[$module][$pageKey];
        }

        return $pageName;
    }

    /**
     * @param UserInterface|null $user
     * @param null $deviceId
     * @return Device|mixed|object|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\ORMException
     */
    private function getOrCreateUserDevice(?UserInterface $user, $deviceId = null)
    {
        $device = null;

        if ($deviceId !== null && $this->deviceManager->isDeviceExists($deviceId) === true) {
            $device = $this->deviceManager->getDevice($deviceId);
        } elseif ($env = $this->envService->getEnvironment()) {
            $device = $this->deviceManager->findDevice($env['ip'], $env['os'], $env['browser']);

            if ($device === null) {
                $device = $this->deviceManager->createDevice($env['ip'], $env['isComputer'], $env['os'], $env['browser'], $env['browserVersion']);
            }
        }

        // Set user to device
        if ($user !== null && $device !== null && $device->getUser() === null) {
            $this->deviceManager->setDeviceUser($device, $user);
        }

        return $device;
    }


    /**
     * @param $haystack
     * @param $needle
     * @param int $offset
     * @return bool
     */
    private function strposa($haystack, $needle, $offset = 0)
    {
        if (!is_array($needle)) $needle = [$needle];

        foreach($needle as $query) {
            if (strpos($haystack, strtolower($query), $offset) !== false) return true; // stop on first true result
        }
        return false;
    }

    /**
     * @param Request $request
     * @return bool
     */
    public function isVisitFromEmail(Request $request)
    {
        return $request->query->has('email_recipient_id') && $request->query->has('email_recipient_type');
    }

    /**
     * @param Request $request
     */
    public function saveClickedEmail(Request $request)
    {
        $id = $request->query->get('email_recipient_id');
        $type = $request->query->get('email_recipient_type');

        $recipient = $type == 'client' ? $this->em->find(Recipient::class, $id)
            : $this->em->find(EmailRecipient::class, $id);

        if ($recipient && !$recipient->isClicked()) {
            $recipient->setIsOpened(true);
            $recipient->setIsClicked(true);
            $recipient->setIsBounced(false);

            $this->em->flush();
        }
    }
}