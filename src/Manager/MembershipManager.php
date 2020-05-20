<?php

namespace App\Manager;

use App\Entity\Building\Building;
use App\Entity\Owner\Contact;
use App\Entity\Owner\Invoice;
use App\Entity\Owner\Email\EmailRecipient;
use App\Entity\Owner\Owner;
use App\Entity\Owner\Vendor;
use App\Entity\Owner\VendorOrder;
use Doctrine\ORM\EntityManagerInterface;

class MembershipManager
{
    private $em;

    private $memberManager;

    public function __construct(EntityManagerInterface $em, MemberManager $manager)
    {
        $this->em = $em;
        $this->memberManager = $manager;
    }

    /**
     * @return MemberManager
     */
    public function getMemberManager()
    {
        return $this->memberManager;
    }

    public function flush()
    {
        $this->em->flush();
    }

    /**
     * @return \App\Entity\Building[]|array
     */
    public function getSoftwareBuildings()
    {
        $buildings = $this->em->getRepository(Building::class)->getSoftwareBuildings();

        return $buildings;
    }

    /**
     * @param $token
     * @return null|Owner|object
     */
    public function findOneByToken($token)
    {
        return $this->em->getRepository(Owner::class)->findByToken($token);
    }

    /**
     * @param $token
     * @return null|Contact|object
     */
    public function findVendorContactByToken($token)
    {
        return $this->em->getRepository(Contact::class)->findOneBy(['token' => $token]);
    }

    /**
     * @param $token
     * @return Building|null|object
     */
    public function findBuildingByToken($token)
    {
        return $this->em->getRepository(Building::class)->findOneBy(['token' => $token]);
    }
    /**
     * @param $id
     * @return Building|null|object
     */
    public function getBuildingById($id)
    {
        return $this->em->find(Building::class, $id);
    }

    /**
     * @param $email
     * @param Building $building
     * @return null|object|Owner
     */
    public function findOneByEmail($email, Building $building = null)
    {
        if ($building) {
            $member = $this->em->getRepository(Owner::class)->findOneBy(['building' => $building, 'email' => $email]);
        } else {
            $member = $this->em->getRepository(Owner::class)->findOneBy(['email' => $email]);
        }

        return $member;
    }

    /**
     * @param VendorOrder $order
     */
    public function createVendorOrder(VendorOrder $order)
    {
        $this->em->persist($order);
        $this->em->flush();
    }

    /**
     * @param Vendor $vendor
     * @return VendorOrder[]|array
     */
    public function getVendorOrders(Vendor $vendor)
    {
        return $this->em->getRepository(VendorOrder::class)->getVendorOrder($vendor);
    }

    /**
     * @param EmailRecipient $recipient
     */
    public function setAsClicked(EmailRecipient $recipient)
    {
        $recipient->setIsClicked(true);
        $this->em->flush();
    }

    /**
     * @param $id
     * @return Invoice|null|object
     */
    public function getInvoice($id)
    {
        return $this->em->find(Invoice::class, $id);
    }

    /**
     * @param Invoice $invoice
     */
    public function removeInvoice(Invoice $invoice)
    {
        $this->em->remove($invoice);
        $this->em->flush();
    }

    /**
     * @return \Doctrine\Common\Collections\Collection|Building[] $buildings
     */
    public function getBuildingsWithPatrons()
    {
        return $this->em->getRepository(Building::class)->getPOSPatrons();
    }
}
