<?php

namespace App\Service;

use App\Entity\User\Device;
use App\Entity\Client\ModuleAccess;
use App\Manager\DeviceManager;
use App\Manager\PageViewManager;
use App\Menu\MenuBuilder;
use Symfony\Component\Security\Core\User\UserInterface;

class PageViewSaver
{
    private $manager;

    private $envService;

    private $deviceManager;

    private $moduleChecker;

    private $menuBuilder;

    public function __construct(
        PageViewManager $pageViewManager,
        EnvironmentService $envService,
        DeviceManager $deviceManager,
        ModuleChecker $moduleChecker,
        MenuBuilder $menuBuilder
    ) {
        $this->manager = $pageViewManager;
        $this->envService = $envService;
        $this->deviceManager = $deviceManager;
        $this->moduleChecker = $moduleChecker;
        $this->menuBuilder = $menuBuilder;
    }

    /**
     * @param UserInterface|null $user
     * @param $url
     * @param null $deviceId
     * @return int|null
     * @throws \Exception
     */
    public function savePageView(?UserInterface $user, $url, $deviceId = null)
    {
        try {
            if ($this->notSaveAblePage($url)) {
                return null;
            }

            if ($pageName = $this->getPageName($url)) {
                $device = $this->getOrCreateUserDevice($deviceId);

                if ($device !== null) {
                    if ($user !== null && $device->getUser() === null) {
                        $this->deviceManager->setDeviceUser($device, $user);
                    }

                    $moduleName = $this->moduleChecker->getModuleNameByUrl($url);
                    $moduleId = $moduleName ? ModuleAccess::getModuleId($moduleName) : null;
                    $viewId = $this->manager->saveView($device->getId(), $url, $moduleId, $pageName);

                    if ($promotionName = $this->getPromotionName($url)) {
                        $this->manager->savePromotionView($viewId, $promotionName);
                    }

                    return $device->getId();
                }
            }
        } catch (\Exception $e) {
            throw $e;
        }

        return $deviceId;
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
     * @param $link
     * @return mixed|string|null
     */
    private function getPageName($link)
    {
        if ($link == '/') {
            return 'landing';
        }

        return $this->getMenuItemName($link);
    }

    /**
     * @param string $link
     * @return mixed|null
     */
    public function getPromotionName(string $link)
    {
        if (!strstr($link, 'promo_link')) {
            return null;
        }

        $parts = parse_url($link);
        parse_str($parts['query'], $query);

        return $query['promo_link'];
    }

    /**
     * @param $link
     * @return string|null
     */
    private function getMenuItemName($link)
    {
        foreach ($this->menuBuilder->getAllMenusItems()->getChildren() as $item) {
            foreach ($item->getChildren() as $child) {
                if ($child->getUri() == $link) {
                    return $child->getName();
                }
            }
        }

        return null;
    }

    /**
     * @param null $deviceId
     * @return Device|mixed|object|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\ORMException
     */
    private function getOrCreateUserDevice($deviceId = null)
    {
        if ($deviceId !== null && $this->deviceManager->isDeviceExists($deviceId)) {
            return $this->deviceManager->getDevice($deviceId);
        }

        $device = null;

        if ($env = $this->envService->getEnvironment()) {
            $device = $this->deviceManager->findDevice($env['ip'], $env['os'], $env['browser']);

            if ($device === null) {
                $device = $this->deviceManager->createDevice($env['ip'], $env['isComputer'], $env['os'], $env['browser'], $env['browserVersion']);
            }
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
}