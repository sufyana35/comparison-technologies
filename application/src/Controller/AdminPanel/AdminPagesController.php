<?php

namespace App\Controller\AdminPanel;

use App\Entity\Pages;
use App\Form\FormManagePageType;
use App\Repository\PagesRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AdminPagesController extends AbstractController
{
    /**
     * View pages
     *
     * @param PagesRepository $pagesRepository
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     *
     * @return Response
     */
    public function pages(
        PagesRepository $pagesRepository,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        if ($request->request->get('Id') && $this->isGranted('ROLE_ADMIN')) {
            $entityManager->remove($pagesRepository->find($request->request->get('Id')));
            $entityManager->flush();
        }

        return $this->render('adminPanel/pages/adminPages.html.twig', [
            'page_name' => 'Pages',
            'pages' => $pagesRepository->getAllPages()
        ]);
    }

    /**
     * Edit page
     *
     * @param integer $pageId
     * @param EntityManagerInterface $entityManager
     * @param Request $request
     * @param PagesRepository $pagesRepository
     * @param ValidatorInterface $validator
     *
     * @return Response
     */
    public function pagesManage(
        int $pageId,
        EntityManagerInterface $entityManager,
        Request $request,
        PagesRepository $pagesRepository,
        ValidatorInterface $validator
    ): Response {
        $page = $pagesRepository->find($pageId);
        $page ?? throw $this->createNotFoundException('The page does not exist');

        $pageForm = $this->createForm(FormManagePageType::class, $page);

        $pageForm->handleRequest($request);
        if ($pageForm->isSubmitted() && $pageForm->isValid()) {
            $page = $pageForm->getData();
            $page->setUpdatedAt(new DateTime());

            $entityManager->persist($page);
            $entityManager->flush();
        }

        if ($pageForm->isSubmitted() && !$pageForm->isValid()) {
            $errors = $validator->validate($pageForm->getData());
            if (count($errors) > 0) {
                $errorMessages = $errors;
            }
        }

        return $this->render('adminPanel/pages/adminManagePage.html.twig', [
            'page_name' => sprintf('Edit page "%s"', $page->getTitle()),
            'page_form' => $pageForm,
            'page' => $page,
            'errors'    => $errorMessages ?? null
        ]);
    }

    /**
     * Add page
     *
     * @param EntityManagerInterface $entityManager
     * @param Request $request
     * @param ValidatorInterface $validator
     *
     * @return Response
     */
    public function pagesAdd(
        EntityManagerInterface $entityManager,
        Request $request,
        ValidatorInterface $validator
    ): Response {
        $pageForm = $this->createForm(FormManagePageType::class, new Pages());

        $pageForm->handleRequest($request);
        if ($pageForm->isSubmitted() && $pageForm->isValid()) {
            $page = $pageForm->getData();
            $page->setUpdatedAt(null);

            $entityManager->persist($page);
            $entityManager->flush();

            return $this->redirectToRoute(
                'pagesManage',
                [
                    'pageId' => $page->getId()
                ]
            );
        }

        if ($pageForm->isSubmitted() && !$pageForm->isValid()) {
            $errors = $validator->validate($pageForm->getData());
            if (count($errors) > 0) {
                $errorMessages = $errors;
            }
        }

        return $this->render('adminPanel/pages/adminManagePage.html.twig', [
            'page_name' => 'Add A New Page',
            'page_form' => $pageForm,
            'errors'    => $errorMessages ?? null
        ]);
    }
}
