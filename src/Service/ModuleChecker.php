<?php

namespace App\Service;

use App\Entity\Client\Client;
use App\Entity\Client\ModuleAccess;

class ModuleChecker
{
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
                case (strstr($url, '/module/customers/')):
                case (strstr($url, '/module/membership/')):
                    $name = 'customers';
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
     * @param Client $client
     * @return bool
     */
    public function isCropsEnabled(Client $client)
    {
        if ($client->getModulesSettings()) {
            foreach ($client->getModulesSettings() as $setting) {
                if ($setting->getName() == 'crops_enabled' && !$setting->isEnabled()) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * @param Client $client
     * @return bool
     */
    public function isSharesEnabled(Client $client)
    {
        if ($client->getModulesSettings()) {
            foreach ($client->getModulesSettings() as $setting) {
                if ($setting->getName() == 'shares_enabled' && !$setting->isEnabled()) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * @param Client $client
     * @return array
     */
    public function getDisabledFeatures(Client $client)
    {
        $disabledFeatures = [];

        if (!$this->isCropsEnabled($client)) $disabledFeatures[] = 'crops';
        if (!$this->isSharesEnabled($client)) $disabledFeatures[] = 'shares';

        return $disabledFeatures;
    }

    /**
     * @param Client $client
     * @return array
     */
    public function getModulesStatuses(Client $client)
    {
        return [
            'lapsed' => $this->getLapsedModules($client),
            'disabled' => $this->getDisabledFeatures($client)
        ];
    }

    /**
     * @param Client $client
     * @param $moduleName
     * @return bool
     */
    public function isModuleActive(Client $client, $moduleName)
    {
        $moduleName = strtolower($moduleName);

        // Module crops is free for now so it works anyway
        if ($moduleName != 'crops') {
            $module = $this->getClientModuleByName($client, $moduleName);
            return $module && $module->getStatusName() != 'LAPSED';
        }

        return true;
    }

    /**
     * @param Client $client
     * @param $moduleName
     * @return ModuleAccess|mixed|null
     */
    public function getClientModuleByName(Client $client, $moduleName)
    {
        $module = null;

        foreach ($client->getAccesses() as $moduleAccess) {
            if ($moduleAccess->getModuleName() == $moduleName) {
                $module = $moduleAccess;
                break;
            }
        }

        return $module;
    }

    /**
     * @param Client $client
     * @param $roles
     * @param $moduleName
     * @return bool
     */
    public function clientHasModuleAccess(Client $client, $roles, $moduleName)
    {
        if (!in_array('ROLE_OWNER', $roles) && !in_array('ROLE_EMPLOYEE', $roles)) {
            return false;
        }

        return $this->isModuleActive($client, $moduleName);
    }

    /**
     * @param Client $client
     * @return array
     */
    public function getLapsedModules(Client $client)
    {
        $lapsed = [];

        foreach ($client->getAccesses() as $moduleAccess) {
            if ($moduleAccess->getStatusName() == 'LAPSED') {
                $lapsed[] = $moduleAccess->getModuleName();
            }
        }

        return $lapsed;
    }
}