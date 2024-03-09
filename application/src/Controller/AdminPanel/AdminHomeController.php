<?php

namespace App\Controller\AdminPanel;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class AdminHomeController extends AbstractController
{
    /**
     * Home
     *
     * @return Response
     */
    public function index(): Response
    {
        return $this->render('adminPanel/adminHome.html.twig', [
            'page_name' => 'Home'
        ]);
    }
}
