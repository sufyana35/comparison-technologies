<?php

namespace App\Controller\AdminPanel;

use App\Entity\Users;
use App\Form\FormRegistrationFormType;
use App\Form\FormUsersManageType;
use App\Repository\UsersRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AdminUsersController extends AbstractController
{
    /**
     * Admin login
     *
     * @param AuthenticationUtils $authenticationUtils
     *
     * @return Response
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('adminPanel/user/adminLogin.html.twig', [
            'page_name' => "Login",
            'last_username' => $lastUsername,
            'error' => $error
        ]);
    }

    /**
     * Register users - Only for Super Admin
     *
     * @param Request $request
     * @param UserPasswordHasherInterface $userPasswordHasher
     * @param EntityManagerInterface $entityManager
     * @param ValidatorInterface $validator
     *
     * @return Response
     */
    public function register(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator
    ): Response {
        $user = new Users();
        $form = $this->createForm(FormRegistrationFormType::class, $user);

        $form->handleRequest($request);

        if (in_array("ROLE_ADMIN", $user->getRoles())) {
            $user->setIsAdministrator(true);
        } else {
            $user->setIsAdministrator(false);
        }

        $user->setCreatedAt(new DateTime());
        $user->setActiveAt(new DateTime());

        if ($form->isSubmitted() && !$form->isValid()) {
            $errors = $validator->validate($form->getData());
            if (count($errors) > 0) {
                $errorMessages = $errors;
            }
        }

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('admin');
        }

        return $this->render('adminPanel/user/adminUsersManage.html.twig', [
            'page_name'        => 'Register User',
            'form' => $form->createView(),
            'errors'           => $errorMessages ?? null
        ]);
    }

    /**
     * Log out
     *
     * @param Security $security
     *
     * @return RedirectResponse
     */
    public function logout(Security $security): RedirectResponse
    {
        $security->logout(false);

        return $this->redirectToRoute('login');
    }

    /**
     * View all registered staff users
     *
     * @param UsersRepository $usersRepository
     *
     * @return Response
     */
    public function users(UsersRepository $usersRepository): Response
    {
        return $this->render('adminPanel/user/adminUsers.html.twig', [
            'page_name' => 'Staff Users',
            'users'     => $usersRepository->findAll()
        ]);
    }

    /**
     * Users delete
     *
     * @param integer $userId
     * @param UsersRepository $usersRepository
     * @param EntityManagerInterface $entityManager
     *
     * @return Response
     */
    public function usersDelete(
        int $userId,
        UsersRepository $usersRepository,
        EntityManagerInterface $entityManager
    ): Response {
        if ($this->isGranted('ROLE_ADMIN')) {
            $entityManager->remove($usersRepository->find($userId));
            $entityManager->flush();
        }

        return $this->redirectToRoute(
            'users'
        );
    }

    public function usersManage(
        UsersRepository $usersRepository,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $userPasswordHasher,
        Request $request,
        int $userId = null,
        ValidatorInterface $validator
    ): Response {
        if ($userId) {
            $usersRepository->find($userId) ?? throw $this->createNotFoundException('The User does not exist');
        }

        $user = $userId ? $usersRepository->find($userId) : new Users();

        $form = $this->createForm(FormUsersManageType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            if (in_array("ROLE_ADMIN", $user->getRoles())) {
                $user->setIsAdministrator(true);
            } else {
                $user->setIsAdministrator(false);
            }

            if ($form->get('plainPassword')->getData()) {
                // encode the plain password
                $user->setPassword(
                    $userPasswordHasher->hashPassword(
                        $user,
                        $form->get('plainPassword')->getData()
                    )
                );
            }

            $entityManager->persist($data);
            $entityManager->flush();

            return $this->redirectToRoute(
                'users'
            );
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $errors = $validator->validate($form->getData());
            if (count($errors) > 0) {
                $errorMessages = $errors;
            }
        }

        return $this->render('adminPanel/user/adminUsersManage.html.twig', [
            'page_name' => 'Users Manage',
            'form'      => $form,
            'errors'    => $errorMessages ?? null
        ]);
    }
}
