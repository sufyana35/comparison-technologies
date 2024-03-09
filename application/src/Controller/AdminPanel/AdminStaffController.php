<?php

namespace App\Controller\AdminPanel;

use App\Repository\UsersRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class AdminStaffController extends AbstractController
{
    /**
     * View all registered staff users
     *
     * @param UsersRepository $usersRepository
     *
     * @return Response
     */
    public function index(UsersRepository $usersRepository): Response
    {
        return $this->render('adminPanel/adminStaff.html.twig', [
            'page_name' => 'Staff Contacts',
            'users'     => $usersRepository->findAll()
        ]);
    }
}
