<?php

namespace App\Controller\AdminPanel;

use App\Entity\BlogCategories;
use App\Form\FormBlogCategoriesManageType;
use App\Repository\BlogCategoriesRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AdminBlogCategoriesController extends AbstractController
{
    /**
     * Show all blog categories
     *
     * @param BlogCategoriesRepository $blogCategoriesRepository
     *
     * @return Response
     */
    public function blogCategories(BlogCategoriesRepository $blogCategoriesRepository): Response
    {
        return $this->render('adminPanel/blog/adminBlogs.html.twig', [
            'page_name' => 'Blog Categories',
            'blogs'     => $blogCategoriesRepository->findAll()
        ]);
    }

    /**
     * Blog delete
     *
     * @param integer $blogId
     * @param BlogCategoriesRepository $blogCategoriesRepository
     * @param EntityManagerInterface $entityManager
     *
     * @return Response
     */
    public function blogCategoriesDelete(
        int $blogId,
        BlogCategoriesRepository $blogCategoriesRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $entityManager->remove($blogCategoriesRepository->find($blogId));
        $entityManager->flush();

        return $this->redirectToRoute(
            'blogCategories'
        );
    }

    /**
     * Manage blog
     *
     * @param BlogCategoriesRepository $blogCategoriesRepository
     * @param EntityManagerInterface $entityManager
     * @param Request $request
     * @param integer $blogId
     * @param ValidatorInterface $validator
     *
     * @return Response
     */
    public function blogCategoriesManage(
        BlogCategoriesRepository $blogCategoriesRepository,
        EntityManagerInterface $entityManager,
        Request $request,
        int $blogId,
        ValidatorInterface $validator
    ): Response {
        $blogCategory = $blogCategoriesRepository->find($blogId);
        $blogCategory ?? throw $this->createNotFoundException('The blog does not exist');

        $blogCategoryForm = $this->createForm(FormBlogCategoriesManageType::class, $blogCategory);
        $blogCategoryForm->handleRequest($request);
        if ($blogCategoryForm->isSubmitted() && $blogCategoryForm->isValid()) {
            $blogCategory = $blogCategoryForm->getData();
            $blogCategory->setUpdatedAt(new DateTime());

            $entityManager->persist($blogCategory);
            $entityManager->flush();
        }

        if ($blogCategoryForm->isSubmitted() && !$blogCategoryForm->isValid()) {
            $errors = $validator->validate($blogCategoryForm->getData());
            if (count($errors) > 0) {
                $errorMessages = $errors;
            }
        }

        return $this->render('adminPanel/blog/adminBlogsManage.html.twig', [
            'page_name' => 'Blog Categories Manage',
            'form'      => $blogCategoryForm,
            'errors'    => $errorMessages ?? null
        ]);
    }

    /**
     * add blog
     *
     * @param EntityManagerInterface $entityManager
     * @param Request $request
     * @param ValidatorInterface $validator
     *
     * @return Response
     */
    public function blogCategoriesAdd(
        EntityManagerInterface $entityManager,
        Request $request,
        ValidatorInterface $validator
    ): Response {
        $blogCategoryForm = $this->createForm(FormBlogCategoriesManageType::class, new BlogCategories());
        $blogCategoryForm->handleRequest($request);
        if ($blogCategoryForm->isSubmitted() && $blogCategoryForm->isValid()) {
            $blogCategory = $blogCategoryForm->getData();
            $blogCategory->setCreatedAt(new DateTime());

            $entityManager->persist($blogCategory);
            $entityManager->flush();
        }

        if ($blogCategoryForm->isSubmitted() && !$blogCategoryForm->isValid()) {
            $errors = $validator->validate($blogCategoryForm->getData());
            if (count($errors) > 0) {
                $errorMessages = $errors;
            }
        }

        return $this->render('adminPanel/blog/adminBlogsManage.html.twig', [
            'page_name' => 'Blog Categories Add',
            'form'      => $blogCategoryForm,
            'errors'    => $errorMessages ?? null
        ]);
    }
}
