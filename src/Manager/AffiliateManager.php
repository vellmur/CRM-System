<?php

namespace App\Manager;

use App\Entity\Building\Referral;
use App\Repository\AffiliateRepository;
use App\Entity\Building\Affiliate;
use Doctrine\ORM\EntityManagerInterface;

class AffiliateManager
{
    private $em;

    private $rep;

    /**
     * AffiliateManager constructor.
     * @param EntityManagerInterface $em
     * @param AffiliateRepository $rep
     */
    public function __construct(EntityManagerInterface $em, AffiliateRepository $rep)
    {
        $this->em = $em;
        $this->rep = $rep;
    }

    /**
     * @param Affiliate $affiliate
     */
    public function createAffiliate(Affiliate $affiliate)
    {
        $this->em->persist($affiliate);
        $this->em->flush();
    }

    /**
     * @param Affiliate $affiliate
     */
    public function updateAffiliate(Affiliate $affiliate)
    {
        $this->em->flush();
    }

    /**
     * @param $affiliate
     * @return mixed
     */
    public function countUnpaidReferrals($affiliate)
    {
        $refNum = $this->em->getRepository(Referral::class)->countUnpaidReferrals($affiliate);

        return $refNum;
    }


    /**
     * @param $affiliate
     * @return mixed
     */
    public function getUnpaidReferrals($affiliate)
    {
        $referrals = $this->em->getRepository(Referral::class)->getUnpaidReferrals($affiliate);

        return $referrals;
    }

    /**
     * @param Referral $referral
     * @param $status
     */
    public function updateReferralPaid(Referral $referral, $status)
    {
        $referral->setIsPaid($status);
        
        $this->em->flush();
    }

    /**
     * @param Affiliate $affiliate
     * @return array
     */
    public function getReferrals(Affiliate $affiliate)
    {
        return $this->em->getRepository(Referral::class)->findBy(['affiliate' => $affiliate]);
    }

    /**
     * @param Affiliate $affiliate
     * @return array
     */
    public function getAllReferrals(Affiliate $affiliate)
    {
        return $this->em->getRepository(Referral::class)->getAllReferrals($affiliate);
    }
    /**
     * @return array
     */
    public function findAll()
    {
        $affiliates = $this->rep->findAllAffiliates();

        return $affiliates;
    }
}