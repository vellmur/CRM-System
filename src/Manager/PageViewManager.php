<?php

namespace App\Manager;

use App\Entity\Customer\Email\EmailRecipient;
use App\Entity\Master\Email\Recipient;
use App\Entity\User\PageView;
use App\Repository\PageViewRepository;
use Doctrine\ORM\EntityManagerInterface;

class PageViewManager
{
    private $em;

    private $rep;

    /**
     * PageViewManager constructor.
     * @param EntityManagerInterface $em
     * @param PageViewRepository $pageViewRepository
     */
    public function __construct(EntityManagerInterface $em, PageViewRepository $pageViewRepository)
    {
        $this->em = $em;
        $this->rep = $pageViewRepository;
    }

    /**
     * @param $deviceId
     * @param $url
     * @param $moduleId
     * @param $pageName
     * @return mixed
     */
    public function saveView($deviceId, $url, $moduleId, $pageName)
    {
        return $this->em->getRepository(PageView::class)->saveView($deviceId, $url, $moduleId, $pageName);
    }

    /**
     * @param int $viewId
     * @param string $promotionName
     * @return mixed
     */
    public function savePromotionView(int $viewId, string $promotionName)
    {
        return $this->em->getRepository(PageView::class)->savePromotionView($viewId, $promotionName);
    }
}