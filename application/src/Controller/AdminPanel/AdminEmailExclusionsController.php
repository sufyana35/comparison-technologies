<?php

namespace App\Controller\AdminPanel;

use App\Entity\EmailExclusions;
use App\Repository\EmailExclusionsRepository;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminEmailExclusionsController extends AbstractController
{
    /**
     * Show all email exclusions
     *
     * @param EmailExclusionsRepository $emailExclusionsRepository
     *
     * @return Response
     */
    public function emailExclusions(EmailExclusionsRepository $emailExclusionsRepository): Response
    {
        return $this->render('adminPanel/emailExclusions/adminEmailExclusions.html.twig', [
            'page_name' => 'Email Exclusions',
            'email_exclusions' => $emailExclusionsRepository->findAll()
        ]);
    }

    /**
     * Email exclusions delete
     *
     * @param EmailExclusionsRepository $emailExclusionsRepository
     * @param EntityManagerInterface $entityManager
     * @param Request $request
     *
     * @return Response
     */
    public function emailExclusionsDelete(
        EmailExclusionsRepository $emailExclusionsRepository,
        EntityManagerInterface $entityManager,
        Request $request
    ): Response {
        if ($this->isGranted('ROLE_ADMIN') || $this->isGranted('ROLE_EMAILS')) {
            $email = (string) $request->request->get('email_address');
            $email = $emailExclusionsRepository->findOneBy(['email' => $email]);

            if ($email) {
                $entityManager->remove($email);
                $entityManager->flush();
            }
        }

        return $this->redirectToRoute(
            'emailExclusions'
        );
    }

    /**
     * Email exclusions add
     *
     * @param EntityManagerInterface $entityManager
     * @param Request $request
     *
     * @return Response
     */
    public function emailExclusionsAdd(
        EntityManagerInterface $entityManager,
        Request $request
    ): Response {
        if ($this->isGranted('ROLE_ADMIN') || $this->isGranted('ROLE_EMAILS')) {
            $email = (string) $request->request->get('email_address');

            try {
                if (!empty($email)) {
                    $emailExclusion = new EmailExclusions();
                    $emailExclusion->setEmail($email);
                    $entityManager->persist($emailExclusion);
                    $entityManager->flush();
                }
            } catch (Exception $emailExclusion) {
            }
        }

        return $this->redirectToRoute(
            'emailExclusions'
        );
    }
}
