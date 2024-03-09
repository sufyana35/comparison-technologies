<?php

namespace App\Controller\AdminPanel;

use App\Entity\ProductGroups;
use App\Form\FormProductGroupsManageType;
use App\Repository\ProductGroupsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AdminProductGroupsController extends AbstractController
{
    /**
     * Show all product groups
     *
     * @param ProductGroupsRepository $productGroupsRepository
     *
     * @return Response
     */
    public function productGroups(ProductGroupsRepository $productGroupsRepository): Response
    {
        return $this->render('adminPanel/productGroups/adminProductGroups.html.twig', [
            'page_name' => 'Product Groups',
            'product_groups' => $productGroupsRepository->findAll()
        ]);
    }

    /**
     * product group delete
     *
     * @param integer $productGroupId
     * @param ProductGroupsRepository $productGroupsRepository
     * @param EntityManagerInterface $entityManager
     *
     * @return Response
     */
    public function productGroupDelete(
        int $productGroupId,
        ProductGroupsRepository $productGroupsRepository,
        EntityManagerInterface $entityManager
    ): Response {
        if ($this->isGranted('ROLE_ADMIN')) {
            $entityManager->remove($productGroupsRepository->find($productGroupId));
            $entityManager->flush();
        }

        return $this->redirectToRoute(
            'productGroups'
        );
    }

    /**
     * Add or Edit product groups
     *
     * @param ProductGroupsRepository $productGroupsRepository
     * @param EntityManagerInterface $entityManager
     * @param Request $request
     * @param integer|null $productGroupId
     * @param ValidatorInterface $validator
     *
     * @return Response
     */
    public function productGroupsManage(
        ProductGroupsRepository $productGroupsRepository,
        EntityManagerInterface $entityManager,
        Request $request,
        int $productGroupId = null,
        ValidatorInterface $validator
    ): Response {
        if ($productGroupId) {
            $productGroupsRepository->find($productGroupId)
            ?? throw $this->createNotFoundException('The Product Group does not exist');
        }

        $productGroup = $productGroupId ? $productGroupsRepository->find($productGroupId) : new ProductGroups();

        $form = $this->createForm(FormProductGroupsManageType::class, $productGroup);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $entityManager->persist($data);
            $entityManager->flush();

            return $this->redirectToRoute(
                'productGroups'
            );
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $errors = $validator->validate($form->getData());
            if (count($errors) > 0) {
                $errorMessages = $errors;
            }
        }

        return $this->render('adminPanel/productGroups/adminProductGroupsManage.html.twig', [
            'page_name' => 'Product Groups Manage',
            'form'      => $form,
            'errors'    => $errorMessages ?? null
        ]);
    }
}
