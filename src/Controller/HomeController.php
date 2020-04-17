<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class HomeController extends AbstractController
{
    /**
     * @return Response
     */
    public function index()
    {
        return $this->render('home/index.html.twig', [
            'bodyClasses' => 'collection-type-index'
        ]);
    }

    /**
     * @return RedirectResponse|Response
     */
    public function mission()
    {
        return $this->render('home/mission.html.twig', [
            'bodyClasses' => 'collection-type-index'
        ]);
    }

    /**
     * @return RedirectResponse|Response
     */
    public function faq()
    {
        return $this->render('home/faq.html.twig', [
            'bodyClasses' => 'collection-type-page'
        ]);
    }

    /**
     * @return RedirectResponse|Response
     */
    public function free()
    {
        return new RedirectResponse('/?promo_link=free');
    }

    /**
     * @return RedirectResponse|Response
     */
    public function eBook()
    {
        return new BinaryFileResponse('eBook.pdf');
    }
}