<?php

namespace App\Service;

use App\Entity\Building\Building;
use App\Entity\Building\ModuleAccess;
use App\Entity\User\User;

class ModuleChecker
{
    /**
     * @param $url
     * @return bool
     */
    public function isWebsiteVisit($url)
    {
        return strstr($url, '/home/') || $url == '/register' || $url == '/login' || $url == strstr($url, '/resetting/');
    }

    /**
     * @param $url
     * @return null|string
     */
    public function getModuleNameByUrl($url)
    {
        $name = null;

        if ($this->isModuleVisit($url)) {
            switch (true)
            {
                case (strstr($url, '/module/owners/')):
                case (strstr($url, '/module/membership/')):
                    $name = 'owners';
                    break;
                default:
                    $name = null;
                    break;
            }
        }

        return $name;
    }

    /**
     * @param $url
     * @return bool
     */
    public function isModuleVisit($url)
    {
        $modules = ModuleAccess::MODULES;

        for ($i = 1; $i <= count($modules); $i++) {
            if (strstr($url, '/module/' . $modules[$i] . '/')) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param Building $building
     * @return array
     */
    public function getModulesStatuses(Building $building)
    {
        return [
            'lapsed' => $this->getLapsedModules($building)
        ];
    }

    /**
     * @param Building $building
     * @param $moduleName
     * @return bool
     */
    public function isModuleActive(Building $building, $moduleName)
    {
        $moduleName = strtolower($moduleName);
        $module = $this->getBuildingModuleByName($building, $moduleName);
        return $module && $module->getStatusName() != 'LAPSED';
    }

    /**
     * @param Building $building
     * @param $moduleName
     * @return ModuleAccess|mixed|null
     */
    public function getBuildingModuleByName(Building $building, $moduleName)
    {
        $module = null;

        foreach ($building->getAccesses() as $moduleAccess) {
            if ($moduleAccess->getModuleName() == $moduleName) {
                $module = $moduleAccess;
                break;
            }
        }

        return $module;
    }

    /**
     * @param Building $building
     * @param $roles
     * @param $moduleName
     * @return bool
     */
    public function buildingHasModuleAccess(Building $building, $roles, $moduleName)
    {
        if (!in_array(User::ROLE_OWNER, $roles) && !in_array(User::ROLE_EMPLOYEE, $roles)) {
            return false;
        }

        return $this->isModuleActive($building, $moduleName);
    }

    /**
     * @param Building $building
     * @return array
     */
    public function getLapsedModules(Building $building)
    {
        $lapsed = [];

        foreach ($building->getAccesses() as $moduleAccess) {
            if ($moduleAccess->getStatusName() == 'LAPSED') {
                $lapsed[] = $moduleAccess->getModuleName();
            }
        }

        return $lapsed;
    }
}