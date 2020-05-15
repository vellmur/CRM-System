<?php

namespace App\Service;

use App\Entity\User\Device;
use App\Entity\Building\ModuleAccess;
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
    public function saveDeviceView(?UserInterface $user, $url, $deviceId = null)
    {
        $moduleId = null;

        if ($this->moduleChecker->isWebsiteVisit($url)) {
            $pageName = $this->getPageNameFromUrl($url);
        } else {
            $pageName = $this->getMenuItemName($url);
            $moduleName = $this->moduleChecker->getModuleNameByUrl($url);
            $moduleId = $moduleName ? ModuleAccess::getModuleId($moduleName) : null;
        }

        if ($pageName && $device = $this->getOrCreateUserDevice($deviceId)) {
            $viewId = $this->manager->saveView($device->getId(), $url, $moduleId, $pageName);

            if ($promotionName = $this->getPromotionName($url)) {
                $this->manager->savePromotionView($viewId, $promotionName);
            }

            if ($user && $device->getUser() == null) {
                $this->deviceManager->saveDeviceUser($device, $user);
            }

            return $device->getId();
        }

        return $deviceId;
    }

    /**
     * @param $url
     * @return mixed|string|string[]
     */
    private function getPageNameFromUrl($url)
    {
        if ($url == '/') {
            return 'landing';
        }

        $slugs = explode('/', strtok($url,'?'));
        $itemName = strlen($slugs[count($slugs) - 1]) > 0 ? $slugs[count($slugs) - 1] : $slugs[count($slugs) - 2];

        // If item name contain numbers (token, id, etc...), set name to previous slug
        if (preg_match('#[0-9]#', $itemName)) {
            $itemName = $slugs[count($slugs) - 2];
        }

        if (strstr($itemName, '-')) $itemName = str_replace('-', ' ', $itemName);

        return $itemName;
    }

    /**
     * @param string $link
     * @return mixed|null
     */
    private function getPromotionName(string $link)
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
}