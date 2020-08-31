<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
}