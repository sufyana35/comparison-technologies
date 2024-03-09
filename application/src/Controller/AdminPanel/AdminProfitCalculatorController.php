<?php

namespace App\Controller\AdminPanel;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class AdminProfitCalculatorController extends AbstractController
{
    public function index(): Response
    {
        return $this->render('adminPanel/adminProfitCalculator.html.twig', [
            'page_name' => 'Profit Calculator'
        ]);
    }
}
