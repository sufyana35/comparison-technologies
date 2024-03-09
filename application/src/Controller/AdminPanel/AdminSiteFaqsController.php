<?php

namespace App\Controller\AdminPanel;

use App\Entity\SiteFaqs;
use App\Form\FormSiteFaqsManageType;
use App\Repository\SiteFaqsRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AdminSiteFaqsController extends AbstractController
{
    /**
     * View FAQs
     *
     * @param SiteFaqsRepository $siteFaqsRepository
     *
     * @return Response
     */
    public function faqs(SiteFaqsRepository $siteFaqsRepository): Response
    {
        return $this->render('adminPanel/faq/adminFaqs.html.twig', [
            'page_name' => 'FAQs',
            'faqs'     => $siteFaqsRepository->findAll()
        ]);
    }

    /**
     * FAQ Delete
     *
     * @param integer $faqId
     * @param SiteFaqsRepository $siteFaqsRepository
     * @param EntityManagerInterface $entityManager
     *
     * @return Response
     */
    public function faqDelete(
        int $faqId,
        SiteFaqsRepository $siteFaqsRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $entityManager->remove($siteFaqsRepository->find($faqId));
        $entityManager->flush();

        return $this->redirectToRoute(
            'faqs'
        );
    }

    /**
     * Edit FAQ
     *
     * @param SiteFaqsRepository $siteFaqsRepository
     * @param EntityManagerInterface $entityManager
     * @param Request $request
     * @param integer $faqId
     * @param ValidatorInterface $validator
     *
     * @return Response
     */
    public function faqManage(
        SiteFaqsRepository $siteFaqsRepository,
        EntityManagerInterface $entityManager,
        Request $request,
        int $faqId,
        ValidatorInterface $validator
    ): Response {
        $siteFaq = $siteFaqsRepository->find($faqId);
        $siteFaq ?? throw $this->createNotFoundException('The site FAQ does not exist');

        $siteFaqForm = $this->createForm(FormSiteFaqsManageType::class, $siteFaq);
        $siteFaqForm->handleRequest($request);
        if ($siteFaqForm->isSubmitted() && $siteFaqForm->isValid()) {
            $siteFaq = $siteFaqForm->getData();
            $siteFaq->setUpdatedAt(new DateTime());

            $entityManager->persist($siteFaq);
            $entityManager->flush();

            return $this->redirectToRoute(
                'faqs'
            );
        }

        if ($siteFaqForm->isSubmitted() && !$siteFaqForm->isValid()) {
            $errors = $validator->validate($siteFaqForm->getData());
            if (count($errors) > 0) {
                $errorMessages = $errors;
            }
        }

        return $this->render('adminPanel/faq/adminFaqManage.html.twig', [
            'page_name' => 'Site FAQ Manage',
            'form'      => $siteFaqForm,
            'errors'    => $errorMessages ?? null
        ]);
    }

    /**
     * Add FAQ
     *
     * @param EntityManagerInterface $entityManager
     * @param Request $request
     * @param ValidatorInterface $validator
     *
     * @return Response
     */
    public function faqAdd(
        EntityManagerInterface $entityManager,
        Request $request,
        ValidatorInterface $validator
    ): Response {
        $siteFaqForm = $this->createForm(FormSiteFaqsManageType::class, new SiteFaqs());
        $siteFaqForm->handleRequest($request);
        if ($siteFaqForm->isSubmitted() && $siteFaqForm->isValid()) {
            $siteFaq = $siteFaqForm->getData();
            $siteFaq->setCreatedAt(new DateTime());

            $entityManager->persist($siteFaq);
            $entityManager->flush();

            return $this->redirectToRoute(
                'faqs'
            );
        }

        if ($siteFaqForm->isSubmitted() && !$siteFaqForm->isValid()) {
            $errors = $validator->validate($siteFaqForm->getData());
            if (count($errors) > 0) {
                $errorMessages = $errors;
            }
        }

        return $this->render('adminPanel/faq/adminFaqManage.html.twig', [
            'page_name' => 'Site FAQ Manage',
            'form'      => $siteFaqForm,
            'errors'    => $errorMessages ?? null
        ]);
    }
}
