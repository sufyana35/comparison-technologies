<?php

namespace App\Controller\AdminPanel;

use App\Entity\Redirects;
use App\Form\FormRedirectsManageType;
use App\Repository\RedirectsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AdminRedirectsController extends AbstractController
{
    /**
     * Show all redirects
     *
     * @param RedirectsRepository $redirectsRepository
     *
     * @return Response
     */
    public function redirects(RedirectsRepository $redirectsRepository): Response
    {
        return $this->render('adminPanel/redirects/adminRedirects.html.twig', [
            'page_name' => 'Redirects',
            'redirects' => $redirectsRepository->findAll()
        ]);
    }

    /**
     * Redirects delete
     *
     * @param integer $redirectId
     * @param RedirectsRepository $redirectsRepository
     * @param EntityManagerInterface $entityManager
     *
     * @return Response
     */
    public function redirectsDelete(
        int $redirectId,
        RedirectsRepository $redirectsRepository,
        EntityManagerInterface $entityManager
    ): Response {
        if ($this->isGranted('ROLE_REDIRECTS')) {
            $entityManager->remove($redirectsRepository->find($redirectId));
            $entityManager->flush();
        }

        return $this->redirectToRoute(
            'redirects'
        );
    }

    /**
     * Add or Edit product groups
     *
     * @param RedirectsRepository $redirectsRepository
     * @param EntityManagerInterface $entityManager
     * @param Request $request
     * @param integer|null $redirectId
     * @param ValidatorInterface $validator
     *
     * @return Response
     */
    public function redirectsManage(
        RedirectsRepository $redirectsRepository,
        EntityManagerInterface $entityManager,
        Request $request,
        int $redirectId = null,
        ValidatorInterface $validator
    ): Response {
        if ($redirectId) {
            $redirectsRepository->find($redirectId)
            ?? throw $this->createNotFoundException('The Product Group does not exist');
        }

        $redirects = $redirectId ? $redirectsRepository->find($redirectId) : new Redirects();

        $form = $this->createForm(FormRedirectsManageType::class, $redirects);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $entityManager->persist($data);
            $entityManager->flush();

            return $this->redirectToRoute(
                'redirects'
            );
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $errors = $validator->validate($form->getData());
            if (count($errors) > 0) {
                $errorMessages = $errors;
            }
        }

        return $this->render('adminPanel/redirects/adminRedirectsManage.html.twig', [
            'page_name' => 'Manage Redirects',
            'form'      => $form,
            'errors'    => $errorMessages ?? null
        ]);
    }
}
