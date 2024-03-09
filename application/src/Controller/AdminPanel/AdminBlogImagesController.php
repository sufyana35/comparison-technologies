<?php

namespace App\Controller\AdminPanel;

use App\Entity\BlogImages;
use App\Form\FormBlogImagesManageType;
use App\Repository\BlogImagesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AdminBlogImagesController extends AbstractController
{
    /**
     * Show all blog images
     *
     * @param BlogImagesRepository $blogImagesRepository
     *
     * @return Response
     */
    public function blogImages(BlogImagesRepository $blogImagesRepository): Response
    {
        return $this->render('adminPanel/blog/adminBlogImages.html.twig', [
            'page_name' => 'Blog Images',
            'blog_images' => $blogImagesRepository->findAll()
        ]);
    }

    /**
     * Blog image delete
     *
     * @param integer $blogImageId
     * @param BlogImagesRepository $blogImagesRepository
     * @param EntityManagerInterface $entityManager
     *
     * @return Response
     */
    public function blogImagesDelete(
        int $blogImageId,
        BlogImagesRepository $blogImagesRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $entityManager->remove($blogImagesRepository->find($blogImageId));
        $entityManager->flush();

        return $this->redirectToRoute(
            'blogImages'
        );
    }

    /**
     * Edit or add blog images
     *
     * @param BlogImagesRepository $blogImagesRepository
     * @param EntityManagerInterface $entityManager
     * @param Request $request
     * @param integer|null $blogImageId
     * @param ValidatorInterface $validator
     *
     * @return Response
     */
    public function blogImagesManage(
        BlogImagesRepository $blogImagesRepository,
        EntityManagerInterface $entityManager,
        Request $request,
        int $blogImageId = null,
        ValidatorInterface $validator
    ): Response {
        if ($blogImageId) {
            $blogImagesRepository->find($blogImageId)
            ?? throw $this->createNotFoundException('The Blog Image does not exist');
        }

        $blogImage = $blogImageId ? $blogImagesRepository->find($blogImageId) : new BlogImages();

        $form = $this->createForm(FormBlogImagesManageType::class, $blogImage);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $entityManager->persist($data);
            $entityManager->flush();

            return $this->redirectToRoute(
                'countries'
            );
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $errors = $validator->validate($form->getData());
            if (count($errors) > 0) {
                $errorMessages = $errors;
            }
        }

        return $this->render('adminPanel/blog/adminBlogImagesManage.html.twig', [
            'page_name' => 'Blog Images Manage',
            'form'      => $form,
            'errors'    => $errorMessages ?? null
        ]);
    }
}
