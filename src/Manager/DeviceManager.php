<?php

namespace App\Manager;

use App\Entity\User\Device;
use App\Entity\User\User;
use App\Repository\DeviceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class DeviceManager
{
    private $em;

    private $repository;

    public function __construct(EntityManagerInterface $em, DeviceRepository $repository)
    {
        $this->em = $em;
        $this->repository = $repository;
    }


    /**
     * @param string $ip
     * @param string $isComputer
     * @param string $os
     * @param string $browser
     * @param string $browserVersion
     * @return Device
     */
    public function createDevice(string $ip, string $isComputer, string $os, string $browser, string $browserVersion)
    {
        $device = new Device();
        $device->setIp($ip);
        $device->setIsComputer($isComputer);
        $device->setOs($os);
        $device->setBrowser($browser);
        $device->setBrowserVersion($browserVersion);

        $this->em->persist($device);
        $this->em->flush();

        return $device;
    }

    /**
     * @param string $ip
     * @param string $os
     * @param string $browser
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findDevice(string $ip, string $os, string $browser)
    {
        return $this->em->getRepository(Device::class)->findDevice($ip, $os, $browser);
    }

    /**
     * @param string $id
     * @return object|null
     * @throws \Doctrine\ORM\ORMException
     */
    public function getDevice(string $id)
    {
        return $this->em->getReference(Device::class, $id);
    }

    /**
     * Device can be not on db if session hold not valid device id
     *
     * @param $deviceId
     * @return bool
     */
    public function isDeviceExists($deviceId)
    {
        return $this->em->getRepository(Device::class)->isDeviceExistsInDb($deviceId);
    }

    /**
     * @param Device $device
     * @param UserInterface $user
     * @throws \Doctrine\ORM\ORMException
     */
    public function saveDeviceUser(Device $device, UserInterface $user)
    {
        $user = $this->em->getReference(User::class, $user->getId());
        $device->setUser($user);
        $this->em->flush();
    }
}