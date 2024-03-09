<?php

namespace App\Controller\AdminPanel;

use App\Entity\Categories;
use App\Form\FormManageCategoriesType;
use App\Helper\HelperImageUploadHandler;
use App\Repository\CategoriesRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AdminCategoriesController extends AbstractController
{
    /**
     * Show all categories
     *
     * @param CategoriesRepository $categoriesRepository
     *
     * @return Response
     */
    public function categories(
        CategoriesRepository $categoriesRepository
    ): Response {
        return $this->render('adminPanel/categories/adminCategories.html.twig', [
            'page_name'          => 'Categories',
            'categories'         => $categoriesRepository->getAllCategories()
        ]);
    }

    /**
     * Categories delete
     *
     * @param integer $categoriesId
     * @param CategoriesRepository $categoriesRepository,
     * @param EntityManagerInterface $entityManager
     *
     * @return Response
     */
    public function categoriesDelete(
        int $categoriesId,
        CategoriesRepository $categoriesRepository,
        EntityManagerInterface $entityManager
    ): Response {
        if ($this->isGranted('ROLE_ADMIN')) {
            $entityManager->remove($categoriesRepository->find($categoriesId));
            $entityManager->flush();
        }

        return $this->redirectToRoute(
            'categories'
        );
    }

    /**
     * Edit Categories
     *
     * @param EntityManagerInterface $entityManager
     * @param Request $request
     * @param integer $categoriesId
     * @param CategoriesRepository $categoriesRepository
     * @param ValidatorInterface $validator
     *
     * @return Response
     */
    public function categoriesManage(
        EntityManagerInterface $entityManager,
        Request $request,
        int $categoriesId,
        CategoriesRepository $categoriesRepository,
        ValidatorInterface $validator
    ): Response {
        $categories = $categoriesRepository->findCategory($categoriesId);
        $categories ?? throw $this->createNotFoundException('The category does not exist');

        $categoriesForm = $this->createForm(FormManageCategoriesType::class, $categories);

        $categoriesForm->handleRequest($request);
        if ($categoriesForm->isSubmitted() && $categoriesForm->isValid()) {
            $categories = $categoriesForm->getData();
            $categories->setUpdatedAt(new DateTime());

            $entityManager->persist($categories);
            $entityManager->flush();

            $iconFile = $categoriesForm->get('icon')->getData();
            if ($iconFile) {
                $imageUploader = new HelperImageUploadHandler();
                $imageUploader->upload($iconFile, $categories->getId(), 'categories', 72, 150, 150);
            }
        }

        if ($categoriesForm->isSubmitted() && !$categoriesForm->isValid()) {
            $errors = $validator->validate($categoriesForm->getData());
            if (count($errors) > 0) {
                $errorMessages = $errors;
            }
        }

        return $this->render('adminPanel/categories/adminCategoriesManage.html.twig', [
            'page_name'                      => 'Edit Category',
            'form_manage_categories_type'    => $categoriesForm,
            'category_products'              => $categoriesRepository->findCategoriesProducts($categoriesId),
            'errors'                         => $errorMessages ?? null
        ]);
    }

    /**
     * Add new category
     *
     * @param EntityManagerInterface $entityManager
     * @param Request $request
     * @param ValidatorInterface $validator
     *
     * @return Response
     */
    public function categoriesAdd(
        EntityManagerInterface $entityManager,
        Request $request,
        ValidatorInterface $validator,
    ): Response {
        $categories = $this->createForm(FormManageCategoriesType::class, new Categories());

        $categories->handleRequest($request);
        if ($categories->isSubmitted() && $categories->isValid()) {
            $iconFile = $categories->get('icon')->getData();

            $categories = $categories->getData();
            $categories->setCreatedAt(new DateTime());

            $entityManager->persist($categories);
            $entityManager->flush();

            if ($iconFile) {
                $imageUploader = new HelperImageUploadHandler();
                $imageUploader->upload($iconFile, $categories->getId(), 'categories', null, 150, 150);
            }

            return $this->redirectToRoute(
                'categoriesManage',
                [
                    'categoriesId' => $categories->getId()
                ]
            );
        }

        if ($categories->isSubmitted() && !$categories->isValid()) {
            $errors = $validator->validate($categories->getData());
            if (count($errors) > 0) {
                $errorMessages = $errors;
            }
        }

        return $this->render('adminPanel/categories/adminCategoriesManage.html.twig', [
            'page_name'                      => 'Add Category',
            'form_manage_categories_type'    => $categories,
            'errors'                         => $errorMessages ?? null
        ]);
    }
}
