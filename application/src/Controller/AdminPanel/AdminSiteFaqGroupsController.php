<?php

namespace App\Controller\AdminPanel;

use App\Entity\SiteFaqGroups;
use App\Form\FormSiteFaqGroupsManageType;
use App\Repository\SiteFaqGroupsRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AdminSiteFaqGroupsController extends AbstractController
{
    /**
     * Show FAQ Groups
     *
     * @param SiteFaqGroupsRepository $siteFaqGroupsRepository
     *
     * @return Response
     */
    public function faqGroups(SiteFaqGroupsRepository $siteFaqGroupsRepository): Response
    {
        return $this->render('adminPanel/faq/adminFaqGroups.html.twig', [
            'page_name' => 'FAQ Groups',
            'faqGroups'     => $siteFaqGroupsRepository->findAll()
        ]);
    }

    /**
     * Delete FAQ Groups
     *
     * @param integer $faqGroupId
     * @param SiteFaqGroupsRepository $siteFaqGroupsRepository
     * @param EntityManagerInterface $entityManager
     *
     * @return Response
     */
    public function faqGroupDelete(
        int $faqGroupId,
        SiteFaqGroupsRepository $siteFaqGroupsRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $entityManager->remove($siteFaqGroupsRepository->find($faqGroupId));
        $entityManager->flush();

        return $this->redirectToRoute(
            'faqGroups'
        );
    }

    /**
     * Edit Faq Group
     *
     * @param SiteFaqGroupsRepository $siteFaqGroupsRepository
     * @param EntityManagerInterface $entityManager
     * @param Request $request
     * @param integer $faqGroupId
     * @param ValidatorInterface $validator
     *
     * @return Response
     */
    public function faqGroupManage(
        SiteFaqGroupsRepository $siteFaqGroupsRepository,
        EntityManagerInterface $entityManager,
        Request $request,
        int $faqGroupId,
        ValidatorInterface $validator
    ): Response {
        $siteFaqGroup = $siteFaqGroupsRepository->find($faqGroupId);
        $siteFaqGroup ?? throw $this->createNotFoundException('The site FAQ Group does not exist');

        $siteFaqGroupForm = $this->createForm(FormSiteFaqGroupsManageType::class, $siteFaqGroup);
        $siteFaqGroupForm->handleRequest($request);
        if ($siteFaqGroupForm->isSubmitted() && $siteFaqGroupForm->isValid()) {
            $faqGroup = $siteFaqGroupForm->getData();
            $faqGroup->setUpdatedAt(new DateTime());

            $entityManager->persist($faqGroup);
            $entityManager->flush();

            return $this->redirectToRoute(
                'faqGroups'
            );
        }

        if ($siteFaqGroupForm->isSubmitted() && !$siteFaqGroupForm->isValid()) {
            $errors = $validator->validate($siteFaqGroupForm->getData());
            if (count($errors) > 0) {
                $errorMessages = $errors;
            }
        }

        return $this->render('adminPanel/faq/adminFaqGroupManage.html.twig', [
            'page_name' => 'Site FAQ Group Manage',
            'form'      => $siteFaqGroupForm,
            'errors'    => $errorMessages ?? null
        ]);
    }

    /**
     * New FAQ Group
     *
     * @param EntityManagerInterface $entityManager
     * @param Request $request
     * @param ValidatorInterface $validator
     *
     * @return Response
     */
    public function faqGroupAdd(
        EntityManagerInterface $entityManager,
        Request $request,
        ValidatorInterface $validator
    ): Response {
        $siteFaqGroupForm = $this->createForm(FormSiteFaqGroupsManageType::class, new SiteFaqGroups());
        $siteFaqGroupForm->handleRequest($request);
        if ($siteFaqGroupForm->isSubmitted() && $siteFaqGroupForm->isValid()) {
            $faqGroup = $siteFaqGroupForm->getData();
            $faqGroup->setCreatedAt(new DateTime());

            $entityManager->persist($faqGroup);
            $entityManager->flush();

            return $this->redirectToRoute(
                'faqGroups'
            );
        }

        if ($siteFaqGroupForm->isSubmitted() && !$siteFaqGroupForm->isValid()) {
            $errors = $validator->validate($siteFaqGroupForm->getData());
            if (count($errors) > 0) {
                $errorMessages = $errors;
            }
        }

        return $this->render('adminPanel/faq/adminFaqGroupManage.html.twig', [
            'page_name' => 'Site FAQ Group Manage',
            'form'      => $siteFaqGroupForm,
            'errors'    => $errorMessages ?? null
        ]);
    }
}
